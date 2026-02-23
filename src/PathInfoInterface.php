<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\PathInfoInterface as BasePathInfoInterface;

interface PathInfoInterface extends BasePathInfoInterface
{
    /**
     * @return string|array<string, string|numeric|bool>|null
     */
    public function getModifiers(): string|array|null;

    /**
     * @param string|array<string, string|numeric|bool>|null $modifiers
     */
    public function withModifiers(string|array|null $modifiers): static;

    /**
     * Creates a new object with encoded modifiers, the modifier will be decoded into an array.
     */
    public function withEncodedModifiers(string $modifiers): static;
}
