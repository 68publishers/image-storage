<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use function is_numeric;

final class Width extends AbstractModifier implements ParsableModifierInterface
{
	protected ?string $alias = 'w';

	public function parseValue(string $value): int
	{
		if (!is_numeric($value)) {
			throw new ModifierException('Width must be a numeric value.');
		}

		return (int) $value;
	}
}
