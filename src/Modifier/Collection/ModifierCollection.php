<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use ArrayIterator;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;

final class ModifierCollection implements ModifierCollectionInterface
{
	/**
	 * (string) name => (object) Modifier
	 *
	 * @var \SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface[]
	 */
	private $modifiers = [];

	/**
	 * (string) alias => (string) name
	 *
	 * @var string[]
	 */
	private $aliases = [];

	/**
	 * {@inheritdoc}
	 */
	public function add(ModifierInterface $modifier): void
	{
		$name = $modifier->getName();
		$alias = $modifier->getAlias();

		if ($this->hasByName($name) || $this->hasByAlias($alias)) {
			throw new InvalidArgumentException(sprintf(
				'Duplicate modifier with name "%s" and alias "%s" passed into %s. Name and alias must be unique.',
				$name,
				$alias,
				__METHOD__
			));
		}

		$this->modifiers[$name] = $modifier;
		$this->aliases[$alias] = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasByName(string $name): bool
	{
		return isset($this->modifiers[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasByAlias(string $alias): bool
	{
		return isset($this->aliases[$alias]) && $this->hasByName($this->aliases[$alias]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByName(string $name): ModifierInterface
	{
		if (!$this->hasByName($name)) {
			throw new InvalidArgumentException(sprintf(
				'Modifier with name "%s" is not defined in collection.',
				$name
			));
		}

		return $this->modifiers[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByAlias(string $alias): ModifierInterface
	{
		if (!$this->hasByAlias($alias)) {
			throw new InvalidArgumentException(sprintf(
				'Modifier with alias "%s" is not defined in collection.',
				$alias
			));
		}

		return $this->modifiers[$this->aliases[$alias]];
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseValues(array $parameters): ModifierValues
	{
		$values = [];

		foreach ($parameters as $k => $v) {
			$modifier = $this->getByAlias($k);

			if ($modifier instanceof ParsableModifierInterface) {
				if (NULL !== ($value = $modifier->parseValue((string) $v))) {
					$values[$modifier->getName()] = $value;
				}

				continue;
			}

			if (TRUE === (bool) $v) {
				$values[$modifier->getName()] = TRUE;
			}
		}

		return new ModifierValues($values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->modifiers);
	}
}
