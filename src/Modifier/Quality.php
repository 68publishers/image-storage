<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Quality extends AbstractModifier implements IParsableModifier
{
	/** @var string  */
	protected $alias = 'q';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): int
	{
		if (!is_numeric($value) || 0 >= ($value = (int) $value) || 100 < $value) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Quality must be int between 0 and 100.'
			));
		}

		return $value;
	}
}
