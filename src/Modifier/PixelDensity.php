<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class PixelDensity extends AbstractModifier implements IParsableModifier
{
	/** @var string  */
	protected $alias = 'pd';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): float
	{
		if (!is_numeric($value)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Pixel density must be numeric.'
			));
		}

		$value = (float) $value;

		if (0 >= $value && 8 < $value) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Pixel density %f is not valid, value must be between 1 and 8.',
				$value
			));
		}

		return $value;
	}
}
