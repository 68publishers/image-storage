<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use Nette;
use SixtyEightPublishers;

final class DefaultLinkGenerator implements ILinkGenerator
{
	use Nette\SmartObject;

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
	public function link(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): string
	{
		$link = sprintf(
			'%s/%s',
			$this->env[SixtyEightPublishers\ImageStorage\Config\Env::BASE_PATH],
			$info->createPath($this->modifierFacade->formatAsString($modifiers))
		);

		if (NULL !== $info->getVersion()
			&& isset($this->env[SixtyEightPublishers\ImageStorage\Config\Env::VERSION_PARAMETER_NAME])
			&& is_string($v = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::VERSION_PARAMETER_NAME])) {
			$link .= empty($v) ? '?' : ('?' . $v . '=');
			$link .= $info->getVersion();
		}
		
		return rawurldecode($link);
	}

	/**
	 * {@inheritdoc}
	 */
	public function srcSet(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): string
	{
		if (NULL === $this->srcSetGenerator) {
			$this->srcSetGenerator = $this->srcSetGeneratorFactory->create($this, $this->modifierFacade);
		}

		return $this->srcSetGenerator->generate($info, $this->modifierFacade->formatAsArray($modifiers));
	}
}
