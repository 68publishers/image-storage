<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use function count;
use function explode;
use function is_numeric;
use function sprintf;

final class AspectRatio extends AbstractModifier implements ParsableModifierInterface
{
    public const KEY_WIDTH = 'w';
    public const KEY_HEIGHT = 'h';

    protected ?string $alias = 'ar';

    /**
     * @return array{w: float, h: float}
     */
    public function parseValue(string $value): array
    {
        $ratio = explode('x', $value);

        if (2 !== count($ratio) || !is_numeric($ratio[0]) || !is_numeric($ratio[1])) {
            throw new ModifierException(sprintf(
                'Value "%s" is not a valid aspect ratio.',
                $value,
            ));
        }

        return [
            self::KEY_WIDTH => (float) $ratio[0],
            self::KEY_HEIGHT => (float) $ratio[1],
        ];
    }
}
