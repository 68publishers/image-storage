<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;

abstract class AbstractModifier implements ModifierInterface
{
	/** @var string  */
	protected $alias;

	/**
	 * @param string|NULL $alias
	 */
	public function __construct(?string $alias = NULL)
	{
		if (NULL !== $alias) {
			$this->alias = $alias;
		}

		if (NULL === $this->alias) {
			throw new InvalidStateException(sprintf(
				'Default value for %s::$alias is not set!',
				static::class
			));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return static::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAlias(): string
	{
		return $this->alias;
	}
}
