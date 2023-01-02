<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use ArrayIterator;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use function sprintf;

final class ModifierCollection implements ModifierCollectionInterface
{
	/** @var array<string, ModifierInterface> */
	private array $modifiers = [];

	/** @var array<string, string> */
	private array $aliases = [];

	public function add(ModifierInterface $modifier): void
	{
		$name = $modifier->getName();
		$alias = $modifier->getAlias();

		if ($this->hasByName($name) || $this->hasByAlias($alias)) {
			throw new InvalidArgumentException(sprintf(
				'Duplicated modifier with the name "%s" and the alias "%s" passed into %s(). Names and the aliases must be unique.',
				$name,
				$alias,
				__METHOD__
			));
		}

		$this->modifiers[$name] = $modifier;
		$this->aliases[$alias] = $name;
	}

	public function hasByName(string $name): bool
	{
		return isset($this->modifiers[$name]);
	}

	public function hasByAlias(string $alias): bool
	{
		return isset($this->aliases[$alias]) && $this->hasByName($this->aliases[$alias]);
	}

	public function getByName(string $name): ModifierInterface
	{
		if (!$this->hasByName($name)) {
			throw new InvalidArgumentException(sprintf(
				'Modifier with the name "%s" is not defined in the collection.',
				$name
			));
		}

		return $this->modifiers[$name];
	}

	public function getByAlias(string $alias): ModifierInterface
	{
		if (!$this->hasByAlias($alias)) {
			throw new InvalidArgumentException(sprintf(
				'Modifier with the alias "%s" is not defined in the collection.',
				$alias
			));
		}

		return $this->modifiers[$this->aliases[$alias]];
	}

	public function parseValues(array $parameters): ModifierValues
	{
		$values = [];

		foreach ($parameters as $k => $v) {
			$modifier = $this->getByAlias($k);

			if ($modifier instanceof ParsableModifierInterface) {
				if (null !== ($value = $modifier->parseValue((string) $v))) {
					$values[$modifier->getName()] = $value;
				}

				continue;
			}

			if (true === (bool) $v) {
				$values[$modifier->getName()] = true;
			}
		}

		return new ModifierValues($values);
	}

	/**
	 * @return ArrayIterator<string, ModifierInterface>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->modifiers);
	}
}
