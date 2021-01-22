<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGenerator as FileLinkGenerator;

final class LinkGenerator extends FileLinkGenerator implements LinkGeneratorInterface
{
	/** @var \SixtyEightPublishers\FileStorage\Config\ConfigInterface  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface  */
	private $srcSetGeneratorFactory;

	/** @var \SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface|NULL  */
	private $signatureStrategy;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator|NULL */
	private $srcSetGenerator;

	/**
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface                      $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface    $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface $srcSetGeneratorFactory
	 * @param \SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface|NULL   $signatureStrategy
	 */
	public function __construct(ConfigInterface $config, ModifierFacadeInterface $modifierFacade, SrcSetGeneratorFactoryInterface $srcSetGeneratorFactory, ?SignatureStrategyInterface $signatureStrategy = NULL)
	{
		parent::__construct($config);

		$this->config = $config;
		$this->modifierFacade = $modifierFacade;
		$this->srcSetGeneratorFactory = $srcSetGeneratorFactory;
		$this->signatureStrategy = $signatureStrategy;
	}

	/**
	 * {@inheritdoc}
	 */
	public function link(FilePathInfoInterface $info): string
	{
		if (!$info instanceof ImagePathInfoInterface) {
			throw new InvalidArgumentException(sprintf(
				'Path info passed into the method %s must be instance of %s.',
				__METHOD__,
				ImagePathInfoInterface::class
			));
		}

		if (NULL === $info->getModifiers()) {
			throw new InvalidArgumentException('Links to the source images can\'t be created.');
		}

		return parent::link($info);
	}

	/**
	 * {@inheritDoc}
	 */
	public function srcSet(ImagePathInfoInterface $info, DescriptorInterface $descriptor): string
	{
		if (NULL === $this->srcSetGenerator) {
			$this->srcSetGenerator = $this->srcSetGeneratorFactory->create($this, $this->modifierFacade);
		}

		return $this->srcSetGenerator->generate($descriptor, $info);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSignatureStrategy(): ?SignatureStrategyInterface
	{
		return $this->signatureStrategy;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function buildQueryParams(FilePathInfoInterface $pathInfo): array
	{
		$params = parent::buildQueryParams($pathInfo);

		if (NULL !== $this->signatureStrategy) {
			$params[] = $this->config[Config::SIGNATURE_PARAMETER_NAME] . '=' . $this->signatureStrategy->createToken($pathInfo->getPath());
		}

		return $params;
	}
}
