<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use Nette;
use SixtyEightPublishers;

final class WDescriptor implements IDescriptor
{
	use Nette\SmartObject;

	/** @var int[]  */
	private $widths;

	/**
	 * @param int ...$widths
	 */
	public function __construct(int ...$widths)
	{
		$this->widths = $widths;
	}

	/**
	 * @param int $min
	 * @param int $max
	 * @param int $step
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor
	 */
	public static function fromRange(int $min, int $max, int $step = 100): self
	{
		if ($max < $min) {
			$tmp = $min;
			$min = $max;
			$max = $tmp;
		}

		$range = range($min, $max, $step);
		$range[] = $max;

		return new static(...array_values(array_unique($range)));
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor **************/

	/**
	 * {@inheritDoc}
	 */
	public function __toString(): string
	{
		return sprintf('W(%s)', implode(',', $this->widths));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultModifiers(): array
	{
		return [
			'w' => min($this->widths),
		];
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade $args
	 *
	 * @return string
	 */
	public function createSrcSet(ArgsFacade $args): string
	{
		$wAlias = $args->getModifierAlias(SixtyEightPublishers\ImageStorage\Modifier\Width::class);

		if (NULL === $wAlias) {
			return $args->createLink($args->getDefaultModifiers());
		}

		$modifiers = $args->getDefaultModifiers() ?? [];

		$links = array_map(static function (int $w) use ($args, $wAlias, $modifiers) {
			$modifiers[$wAlias] = $w;

			return sprintf(
				'%s %dw',
				$args->createLink($modifiers),
				$w
			);
		}, $this->widths);

		return implode(', ', $links);
	}
}
