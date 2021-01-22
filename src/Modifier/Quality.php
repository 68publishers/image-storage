<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

final class Quality extends AbstractModifier implements ParsableModifierInterface
{
	/** @var string  */
	protected $alias = 'q';

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value) || 0 >= ($value = (int) $value) || 100 < $value) {
			throw new ModifierException(sprintf(
				'Quality must be int between 0 and 100.'
			));
		}

		return $value;
	}
}
