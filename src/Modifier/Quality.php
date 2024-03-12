<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use function is_numeric;

final class Quality extends AbstractModifier implements ParsableModifierInterface
{
    protected ?string $alias = 'q';

    public function parseValue(string $value): int
    {
        if (!is_numeric($value) || 0 >= ($value = (int) $value) || 100 < $value) {
            throw new ModifierException('Quality must be an int between 1 and 100.');
        }

        return $value;
    }
}
