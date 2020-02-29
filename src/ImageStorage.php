<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use Nette;
use SixtyEightPublishers;

final class ImageStorage implements IImageStorage
{
	use Nette\SmartObject;

	/** @var string  */
	private $name;

	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider  */
	private $noImageProvider;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver  */
	private $noImageResolver;

	/** @var \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory  */
	private $resourceFactory;

	/** @var \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister  */
	private $imagePersister;

	/** @var \SixtyEightPublishers\ImageStorage\ImageServer\IImageServer  */
	private $imageServer;

	/**
	 * @param string                                                            $name
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator   $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider       $noImageProvider
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver       $noImageResolver
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory      $resourceFactory
	 * @param \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister $imagePersister
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\IImageServer       $imageServer
	 */
	public function __construct(
		string $name,
		LinkGenerator\ILinkGenerator $linkGenerator,
		NoImage\INoImageProvider $noImageProvider,
		NoImage\INoImageResolver $noImageResolver,
		Resource\IResourceFactory $resourceFactory,
		ImagePersister\IImagePersister $imagePersister,
		ImageServer\IImageServer $imageServer
	) {
		$this->name = $name;
		$this->linkGenerator = $linkGenerator;
		$this->noImageProvider = $noImageProvider;
		$this->noImageResolver = $noImageResolver;
		$this->resourceFactory = $resourceFactory;
		$this->imagePersister = $imagePersister;
		$this->imageServer = $imageServer;
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\IImageStorage ****************/

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function createImageInfo(string $path): ImageInfo
	{
		return new ImageInfo($path, $this->isNoImage($path));
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSignatureStrategy(?SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy $signatureStrategy): void
	{
		$this->linkGenerator->setSignatureStrategy($signatureStrategy);
		$this->imageServer->setSignatureStrategy($signatureStrategy);
	}

	/**
	 * {@inheritdoc}
	 */
	public function link(ImageInfo $info, $modifiers): string
	{
		return $this->linkGenerator->link($info, $modifiers);
	}

	/**
	 * {@inheritdoc}
	 */
	public function srcSet(ImageInfo $info, Responsive\Descriptor\IDescriptor $descriptor, $modifiers = NULL): string
	{
		return $this->linkGenerator->srcSet($info, $descriptor, $modifiers);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNoImage(?string $name = NULL): ImageInfo
	{
		return $this->noImageProvider->getNoImage($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isNoImage(string $path): bool
	{
		return $this->noImageProvider->isNoImage($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveNoImage(string $path): ImageInfo
	{
		return $this->noImageResolver->resolveNoImage($path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createResource(ImageInfo $info, $modifier = NULL): Resource\IResource
	{
		return $this->resourceFactory->createResource($info);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createResourceFromLocalFile(ImageInfo $info, string $filename, $modifier = NULL): Resource\IResource
	{
		return $this->resourceFactory->createResourceFromLocalFile($info, $filename);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem(): SixtyEightPublishers\ImageStorage\Filesystem
	{
		return $this->imagePersister->getFilesystem();
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(ImageInfo $info, $modifiers = NULL): bool
	{
		return $this->imagePersister->exists($info, $modifiers);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(Resource\IResource $resource, $modifiers = NULL, array $config = []): string
	{
		return $this->imagePersister->save($resource, $modifiers, $config);
	}

	/**
	 * {@inheritdoc}
	 */
	public function update(Resource\IResource $resource, array $config = []): string
	{
		return $this->imagePersister->update($resource, $config);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(ImageInfo $info, bool $cacheOnly = FALSE): void
	{
		$this->imagePersister->delete($info, $cacheOnly);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse
	{
		return $this->imageServer->getImageResponse($request);
	}
}
