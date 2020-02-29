<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Width extends AbstractModifier implements IParsableModifier
{
	/** @var string  */
	protected $alias = 'w';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Width must be numeric value.'
			));
		}

		return (int) $value;
	}
}
