<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class AspectRatio extends AbstractModifier implements IParsableModifier
{
	public const    KEY_WIDTH = 'w',
					KEY_HEIGHT = 'h';

	/** @var string  */
	protected $alias = 'ar';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

	/**
	 * {@inheritdoc}
	 *
	 * @return float[]
	 */
	public function parseValue(string $value): array
	{
		$ratio = explode('x', $value);

		if (2 !== count($ratio) || !is_numeric($ratio[0]) || !is_numeric($ratio[1])) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'A value "%s" is not a valid aspect ratio.',
				$value
			));
		}

		return [
			self::KEY_WIDTH => (float) $ratio[0],
			self::KEY_HEIGHT => (float) $ratio[1],
		];
	}
}
