<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use Stringable;

interface DescriptorInterface extends Stringable
{
    /**
     * @param string|numeric|bool $value
     *
     * @return string|numeric|bool
     * @throws InvalidArgumentException
     */
    public function validateModifierValue(
        mixed $value,
        mixed $default,
    ): mixed;

    /**
     * @param string|numeric|bool $value
     *
     * @return array<string, string|numeric|bool>
     * @throws InvalidArgumentException
     */
    public function expandModifier(
        ModifierCollectionInterface $modifierCollection,
        mixed $value,
    ): array;

    /**
     * @return iterable<array<string, string|numeric|bool>>
     */
    public function iterateModifiers(
        ModifierCollectionInterface $modifierCollection,
    ): iterable;

    public function createSrcSet(ArgsFacade $args): SrcSet;
}
