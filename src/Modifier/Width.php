<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

final class Width extends AbstractModifier implements ParsableModifierInterface
{
	/** @var string  */
	protected $alias = 'w';

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value)) {
			throw new ModifierException(sprintf(
				'Width must be numeric value.'
			));
		}

		return (int) $value;
	}
}
