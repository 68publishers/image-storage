<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use function assert;
use function in_array;
use function is_array;
use function is_int;
use function sprintf;

final class AllowedQualityValidator implements ValidatorInterface
{
    public function validate(ModifierValues $values, ConfigInterface $config): void
    {
        $allowedQualities = $config[Config::ALLOWED_QUALITIES];

        if (!is_array($allowedQualities) || empty($allowedQualities)) {
            return;
        }

        $quality = $values->getOptional(Quality::class);
        assert(null === $quality || is_int($quality));

        if (null !== $quality && !in_array($quality, $allowedQualities, false)) {
            throw new ModifierException(sprintf(
                'Invalid quality modifier, %s is not supported.',
                $quality,
            ));
        }
    }
}
