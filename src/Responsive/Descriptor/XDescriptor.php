<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use function implode;
use function sprintf;
use function in_array;
use function array_map;
use function array_unshift;
use function number_format;

final class XDescriptor implements DescriptorInterface
{
	/** @var array<float> */
	private array $pixelDensities;

	/**
	 * @param int|float|string ...$pixelDensities
	 */
	public function __construct(...$pixelDensities)
	{
		$pixelDensities = array_map('floatval', $pixelDensities);

		if (!in_array(1.0, $pixelDensities, true)) {
			array_unshift($pixelDensities, 1.0);
		}

		$this->pixelDensities = $pixelDensities;
	}

	public static function default(): self
	{
		return new self(1, 2, 3);
	}

	public function __toString(): string
	{
		return sprintf('X(%s)', implode(',', $this->pixelDensities));
	}

	public function createSrcSet(ArgsFacade $args): string
	{
		$pdAlias = $args->getModifierAlias(PixelDensity::class);
		$modifiers = $args->getDefaultModifiers() ?? [];

		if (null === $pdAlias) {
			return empty($modifiers) ? '' : $args->createLink($modifiers);
		}

		$links = array_map(static function (float $pd) use ($args, $pdAlias, $modifiers) {
			$modifiers[$pdAlias] = $pd;

			return sprintf(
				'%s%s',
				$args->createLink($modifiers),
				1.0 === $pd ? '' : (' ' . number_format($pd, 1, '.', '') . 'x')
			);
		}, $this->pixelDensities);

		return implode(', ', $links);
	}
}
