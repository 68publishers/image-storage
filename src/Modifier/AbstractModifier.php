<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use function sprintf;

abstract class AbstractModifier implements ModifierInterface
{
	protected ?string $alias = null;

	public function __construct(?string $alias = null)
	{
		if (null !== $alias) {
			$this->alias = $alias;
		}

		if (null === $this->alias) {
			throw new InvalidStateException(sprintf(
				'Default value for %s::$alias is not set!',
				static::class
			));
		}
	}

	public function getName(): string
	{
		return static::class;
	}

	public function getAlias(): string
	{
		return (string) $this->alias;
	}
}
