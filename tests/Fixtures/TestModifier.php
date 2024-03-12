<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Fixtures;

use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;

final class TestModifier implements ModifierInterface
{
    public function getName(): string
    {
        return self::class;
    }

    public function getAlias(): string
    {
        return 'test';
    }
}
