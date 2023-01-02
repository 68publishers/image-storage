<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use function is_numeric;

final class PixelDensity extends AbstractModifier implements ParsableModifierInterface
{
	protected ?string $alias = 'pd';

	public function parseValue(string $value): float
	{
		if (!is_numeric($value)) {
			throw new ModifierException('Pixel density must be a numeric value.');
		}

		$value = (float) $value;

		if (0 >= $value || 8 < $value) {
			throw new ModifierException(sprintf(
				'Pixel density %.1f is not valid, the value must be between 1 and 8.',
				$value
			));
		}

		return $value;
	}
}
