<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;

interface ModifierCollectionInterface extends \IteratorAggregate
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface $modifier
	 */
	public function add(ModifierInterface $modifier): void;

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
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface
	 */
	public function getByName(string $name): ModifierInterface;

	/**
	 * @param string $alias
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface
	 */
	public function getByAlias(string $alias): ModifierInterface;

	/**
	 * @param array $parameters
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues
	 */
	public function parseValues(array $parameters): ModifierValues;
}
