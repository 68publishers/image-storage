<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use Nette;
use SixtyEightPublishers;

final class DefaultLinkGenerator implements ILinkGenerator
{
	use Nette\SmartObject,
		SixtyEightPublishers\ImageStorage\Security\TSignatureStrategyAware;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory  */
	private $srcSetGeneratorFactory;

	/** @var NULL|\SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator */
	private $srcSetGenerator;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                         $env
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade    $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory $srcSetGeneratorFactory
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory $srcSetGeneratorFactory
	) {
		$this->env = $env;
		$this->modifierFacade = $modifierFacade;
		$this->srcSetGeneratorFactory = $srcSetGeneratorFactory;
	}

	/************ interface \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator ************/

	/**
	 * {@inheritdoc}
	 */
	public function link(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers): string
	{
		$basePath = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::BASE_PATH];
		$path = $info->createCachedPath($this->modifierFacade->formatAsString($modifiers));
		$link = (!empty($basePath) ? '/' : '') . $basePath . '/' . $path;
		$params = [];

		if (NULL !== $this->signatureStrategy) {
			$params[] = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::SIGNATURE_PARAMETER_NAME] . '=' . $this->signatureStrategy->createToken($path);
		}

		if (NULL !== $info->getVersion()) {
			$versionParameterName = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::VERSION_PARAMETER_NAME];
			$params[] = empty($versionParameterName) ? $info->getVersion() : ($versionParameterName . '=' . $info->getVersion());
		}

		if (0 < count($params)) {
			$link .= '?' . implode('&', $params);
		}

		if (!empty($this->env[SixtyEightPublishers\ImageStorage\Config\Env::HOST])) {
			$link = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::HOST] . $link;
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
