<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use SixtyEightPublishers;

interface IModifierCollection extends \IteratorAggregate
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\IModifier $modifier
	 */
	public function add(SixtyEightPublishers\ImageStorage\Modifier\IModifier $modifier): void;

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasByName(string $name): bool;

	/**
	 * @param string $alias
	 *
	 * @return bool
	 */
	public function hasByAlias(string $alias): bool;

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\IModifier
	 */
	public function getByName(string $name): SixtyEightPublishers\ImageStorage\Modifier\IModifier;

	/**
	 * @param string $alias
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\IModifier
	 */
	public function getByAlias(string $alias): SixtyEightPublishers\ImageStorage\Modifier\IModifier;

	/**
	 * @param array $parameters
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues
	 */
	public function parseValues(array $parameters): ModifierValues;
}
