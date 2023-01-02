<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use function sprintf;
use function in_array;

final class Orientation extends AbstractModifier implements ParsableModifierInterface
{
	private const VALUES = [ 'auto', '0', '90', '-90', '180', '-180', '270', '-270' ];

	protected ?string $alias = 'o';

	public function parseValue(string $value): string|int
	{
		if (!in_array($value, self::VALUES, true)) {
			throw new ModifierException(sprintf(
				'Value "%s" is not a valid orientation.',
				$value
			));
		}

		return 'auto' === $value ? $value : (int) $value;
	}
}
