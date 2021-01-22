<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;

final class SrcSetGenerator
{
	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/** @var array  */
	private $results = [];

	/**
	 * @param \SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface     $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 */
	public function __construct(LinkGeneratorInterface $linkGenerator, ModifierFacadeInterface $modifierFacade)
	{
		$this->linkGenerator = $linkGenerator;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface $descriptor
	 * @param \SixtyEightPublishers\ImageStorage\PathInfoInterface                         $pathInfo
	 *
	 * @return string
	 */
	public function generate(DescriptorInterface $descriptor, PathInfoInterface $pathInfo): string
	{
		$key = $descriptor . '::' . $pathInfo;

		if (array_key_exists($key, $this->results)) {
			return $this->results[$key];
		}

		return $this->results[$key] = $descriptor->createSrcSet(new ArgsFacade(
			$this->linkGenerator,
			$this->modifierFacade,
			$pathInfo
		));
	}
}
