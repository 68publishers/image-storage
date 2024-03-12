<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

interface ParsableModifierInterface extends ModifierInterface
{
    /**
     * Returns NULL if value is not valid, otherwise returns original passed or modified value.
     */
    public function parseValue(string $value): mixed;
}
