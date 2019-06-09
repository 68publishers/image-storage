<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use Nette;
use SixtyEightPublishers;

final class ModifierCollection implements IModifierCollection
{
	use Nette\SmartObject;

	/**
	 * (string) name => (object) Modifier
	 *
	 * @var \SixtyEightPublishers\ImageStorage\Modifier\IModifier[]
	 */
	private $modifiers = [];

	/**
	 * (string) alias => (string) name
	 *
	 * @var string[]
	 */
	private $aliases = [];

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection **************/

	/**
	 * {@inheritdoc}
	 */
	public function add(SixtyEightPublishers\ImageStorage\Modifier\IModifier $modifier): void
	{
		$name = $modifier->getName();
		$alias = $modifier->getAlias();

		if ($this->hasByName($name) || $this->hasByAlias($alias)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
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
	public function getByName(string $name): SixtyEightPublishers\ImageStorage\Modifier\IModifier
	{
		if (!$this->hasByName($name)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Modifier with name "%s" is not defined in collection.',
				$name
			));
		}

		return $this->modifiers[$name];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByAlias(string $alias): SixtyEightPublishers\ImageStorage\Modifier\IModifier
	{
		if (!$this->hasByAlias($alias)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
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

			if (NULL !== ($value = $modifier->parseValue($v))) {
				$values[$modifier->getName()] = $value;
			}
		}

		return new ModifierValues($values);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->modifiers);
	}
}
