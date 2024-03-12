<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Fixtures;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface;

final class TestValidator implements ValidatorInterface
{
    public function validate(ModifierValues $values, ConfigInterface $config): void
    {
    }
}
