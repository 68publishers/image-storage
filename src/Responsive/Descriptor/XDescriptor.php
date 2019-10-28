<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use Nette;
use SixtyEightPublishers;

final class XDescriptor implements IDescriptor
{
	use Nette\SmartObject;

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

	/************** interface \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor **************/

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		return sprintf('X(%s)', implode(',', $this->pixelDensities));
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade $args
	 *
	 * @return string
	 */
	public function createSrcSet(ArgsFacade $args): string
	{
		$pdAlias = $args->getModifierAlias(SixtyEightPublishers\ImageStorage\Modifier\PixelDensity::class);

		if (NULL === $pdAlias) {
			return $args->createLink($args->getDefaultModifiers());
		}

		$modifiers = $args->getDefaultModifiers() ?? [];

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
