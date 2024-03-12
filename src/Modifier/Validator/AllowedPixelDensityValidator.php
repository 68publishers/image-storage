<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use function assert;
use function in_array;
use function is_array;
use function is_float;
use function sprintf;

final class AllowedPixelDensityValidator implements ValidatorInterface
{
    public function validate(ModifierValues $values, ConfigInterface $config): void
    {
        $allowedPixelDensities = $config[Config::ALLOWED_PIXEL_DENSITY];

        if (!is_array($allowedPixelDensities) || empty($allowedPixelDensities)) {
            return;
        }

        $pd = $values->getOptional(PixelDensity::class);
        assert(null === $pd || is_float($pd));

        if (null !== $pd && !in_array($pd, $allowedPixelDensities, false)) {
            throw new ModifierException(sprintf(
                'Invalid pixel density modifier, %.1f is not supported.',
                $pd,
            ));
        }
    }
}
