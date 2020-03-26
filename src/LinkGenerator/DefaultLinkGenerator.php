<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use Nette;
use SixtyEightPublishers;

final class DefaultLinkGenerator implements ILinkGenerator
{
	use Nette\SmartObject,
		SixtyEightPublishers\ImageStorage\Security\TSignatureStrategyAware;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory  */
	private $srcSetGeneratorFactory;

	/** @var NULL|\SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator */
	private $srcSetGenerator;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config                      $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade    $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory $srcSetGeneratorFactory
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Config $config,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory $srcSetGeneratorFactory
	) {
		$this->config = $config;
		$this->modifierFacade = $modifierFacade;
		$this->srcSetGeneratorFactory = $srcSetGeneratorFactory;
	}

	/************ interface \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator ************/

	/**
	 * {@inheritdoc}
	 */
	public function link(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers): string
	{
		$basePath = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::BASE_PATH];
		$path = $info->createCachedPath($this->modifierFacade->formatAsString($modifiers));
		$link = (!empty($basePath) ? '/' : '') . $basePath . '/' . $path;
		$params = [];

		if (NULL !== $this->signatureStrategy) {
			$params[] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_PARAMETER_NAME] . '=' . $this->signatureStrategy->createToken($path);
		}

		if (NULL !== $info->getVersion()) {
			$versionParameterName = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::VERSION_PARAMETER_NAME];
			$params[] = empty($versionParameterName) ? $info->getVersion() : ($versionParameterName . '=' . $info->getVersion());
		}

		if (0 < count($params)) {
			$link .= '?' . implode('&', $params);
		}

		if (!empty($this->config[SixtyEightPublishers\ImageStorage\Config\Config::HOST])) {
			$link = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::HOST] . $link;
		}

		return rawurldecode($link);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\JsonException
	 */
	public function srcSet(SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor, $modifiers = NULL): string
	{
		if (NULL === $this->srcSetGenerator) {
			$this->srcSetGenerator = $this->srcSetGeneratorFactory->create($this, $this->modifierFacade);
		}

		return $this->srcSetGenerator->generate($descriptor, $info, NULL === $modifiers ? [] : $this->modifierFacade->formatAsArray($modifiers));
	}
}
