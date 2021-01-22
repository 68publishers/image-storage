<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

final class Height extends AbstractModifier implements ParsableModifierInterface
{
	/** @var string  */
	protected $alias = 'h';

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value)) {
			throw new ModifierException(sprintf(
				'Height must be numeric value.'
			));
		}

		return (int) $value;
	}
}
