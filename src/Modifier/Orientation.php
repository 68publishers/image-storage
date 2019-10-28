<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Orientation extends AbstractModifier
{
	private const VALUES = [ 'auto', '0', '90', '-90', '180', '-180', '270', '-270' ];

	/** @var string  */
	protected $alias = 'o';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): string
	{
		if (!in_array($value, self::VALUES, TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Value "%s" is not valid orientation',
				$value
			));
		}

		return $value;
	}
}
