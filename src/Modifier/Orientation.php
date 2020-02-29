<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Orientation extends AbstractModifier implements IParsableModifier
{
	private const VALUES = [ 'auto', '0', '90', '-90', '180', '-180', '270', '-270' ];

	/** @var string  */
	protected $alias = 'o';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

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
