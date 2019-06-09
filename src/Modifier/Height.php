<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Height extends AbstractModifier
{
	/** @var string  */
	protected $alias = 'h';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Height must be numeric value.'
			));
		}

		return (int) $value;
	}
}
