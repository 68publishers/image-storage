<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class PreserveFormat extends AbstractModifier
{
	const 	VALUES_TRUE = [ 1, '1', TRUE ],
			VALUES_FALSE = [ 0, '0', FALSE ];

	/** @var string  */
	protected $alias = 'pf';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): bool
	{
		if (!in_array($value, [ '0', '1' ], TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'PreserveFormat\'s value must be 0 or 1.'
			));
		}

		return (bool) $value;
	}
}
