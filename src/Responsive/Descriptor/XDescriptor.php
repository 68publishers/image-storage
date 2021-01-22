<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;

final class XDescriptor implements DescriptorInterface
{
	/** @var float[]  */
	private $pixelDensities;

	/**
	 * @param int|float|string ...$pixelDensities
	 */
	public function __construct(...$pixelDensities)
	{
		$pixelDensities = array_map('floatval', $pixelDensities);

		if (!in_array(1.0, $pixelDensities, TRUE)) {
			array_unshift($pixelDensities, 1.0);
		}

		$this->pixelDensities = $pixelDensities;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor
	 */
	public static function default(): self
	{
		return new static(1, 2, 3);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		return sprintf('X(%s)', implode(',', $this->pixelDensities));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultModifiers(): array
	{
		return [
			'pd' => min($this->pixelDensities),
		];
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade $args
	 *
	 * @return string
	 */
	public function createSrcSet(ArgsFacade $args): string
	{
		$pdAlias = $args->getModifierAlias(PixelDensity::class);
		$modifiers = $args->getDefaultModifiers() ?? [];

		if (NULL === $pdAlias) {
			return empty($modifiers) ? '' : $args->createLink($modifiers);
		}

		$links = array_map(static function (float $pd) use ($args, $pdAlias, $modifiers) {
			$modifiers[$pdAlias] = $pd;

			return sprintf(
				'%s %s',
				$args->createLink($modifiers),
				1.0 === $pd ? '' : number_format($pd, 1, '.', '') . 'x'
			);
		}, $this->pixelDensities);

		return implode(', ', $links);
	}
}
