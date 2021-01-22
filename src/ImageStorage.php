<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\FileStorage;
use SixtyEightPublishers\FileStorage\FileInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;

final class ImageStorage extends FileStorage implements ImageStorageInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface  */
	private $noImageResolver;

	/** @var \SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface  */
	private $infoFactory;

	/** @var \SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface  */
	private $imageServerFactory;

	/** @var \SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface|NULL */
	private $imageServer;

	/**
	 * @param string                                                                     $name
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface                   $config
	 * @param \SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface        $resourceFactory
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface    $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface     $imagePersister
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface        $noImageResolver
	 * @param \SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface               $infoFactory
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface $imageServerFactory
	 */
	public function __construct(
		string $name,
		ConfigInterface $config,
		ResourceFactoryInterface $resourceFactory,
		LinkGeneratorInterface $linkGenerator,
		ImagePersisterInterface $imagePersister,
		NoImageResolverInterface $noImageResolver,
		InfoFactoryInterface $infoFactory,
		ImageServerFactoryInterface $imageServerFactory
	) {
		parent::__construct($name, $config, $resourceFactory, $linkGenerator, $imagePersister);

		$this->noImageResolver = $noImageResolver;
		$this->infoFactory = $infoFactory;
		$this->imageServerFactory = $imageServerFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createPathInfo(string $path, $modifier = NULL): FilePathInfoInterface
	{
		return $this->infoFactory->createPathInfo($path, $modifier);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function createFileInfo(FilePathInfoInterface $pathInfo): FileInfoInterface
	{
		if (!$pathInfo instanceof PathInfoInterface) {
			$pathInfo = $this->createPathInfo($pathInfo->getPath());
		}

		return $this->infoFactory->createFileInfo($pathInfo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getImageResponse(RequestInterface $request)
	{
		if (NULL === $this->imageServer) {
			$this->imageServer = $this->imageServerFactory->create($this);
		}

		return $this->imageServer->getImageResponse($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNoImageConfig(): NoImageConfigInterface
	{
		return $this->noImageResolver->getNoImageConfig();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNoImage(?string $name = NULL): PathInfoInterface
	{
		return $this->noImageResolver->getNoImage($name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isNoImage(string $path): bool
	{
		return $this->noImageResolver->isNoImage($path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resolveNoImage(string $path): PathInfoInterface
	{
		return $this->noImageResolver->resolveNoImage($path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function srcSet(PathInfoInterface $info, DescriptorInterface $descriptor): string
	{
		return $this->linkGenerator->srcSet($info, $descriptor);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSignatureStrategy(): ?SignatureStrategyInterface
	{
		return $this->linkGenerator->getSignatureStrategy();
	}
}
