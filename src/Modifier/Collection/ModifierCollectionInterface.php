<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use IteratorAggregate;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;

/**
 * @extends IteratorAggregate<string, ModifierInterface>
 */
interface ModifierCollectionInterface extends IteratorAggregate
{
    /**
     * @throws InvalidArgumentException
     */
    public function add(ModifierInterface $modifier): void;

    public function hasByName(string $name): bool;

    public function hasByAlias(string $alias): bool;

    /**
     * @throws InvalidArgumentException
     */
    public function getByName(string $name): ModifierInterface;

    /**
     * @throws InvalidArgumentException
     */
    public function getByAlias(string $alias): ModifierInterface;

    /**
     * @param array<string, scalar> $parameters
     */
    public function parseValues(array $parameters): ModifierValues;
}
