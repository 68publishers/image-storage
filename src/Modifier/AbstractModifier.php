<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use Nette;
use SixtyEightPublishers;

abstract class AbstractModifier implements IModifier
{
	use Nette\SmartObject;

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
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'Default value for %s::$alias is not set!',
				static::class
			));
		}
	}

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IModifier ******************/

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
