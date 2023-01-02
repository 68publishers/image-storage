<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use function array_key_exists;

final class SrcSetGenerator
{
	/** @var array<string, string> */
	private array $results = [];

	public function __construct(
		private readonly LinkGeneratorInterface $linkGenerator,
		private readonly ModifierFacadeInterface $modifierFacade,
	) {
	}

	public function generate(DescriptorInterface $descriptor, PathInfoInterface $pathInfo): string
	{
		$key = $descriptor . '::' . (empty($pathInfo->getModifiers()) ? $pathInfo->withModifiers(['original' => true]) : $pathInfo);

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
