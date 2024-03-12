<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use function array_intersect;
use function assert;
use function count;
use function is_array;
use function is_int;

final class AllowedResolutionValidator implements ValidatorInterface
{
    public function validate(ModifierValues $values, ConfigInterface $config): void
    {
        $allowedResolutions = $config[Config::ALLOWED_RESOLUTIONS];

        if (!is_array($allowedResolutions) || empty($allowedResolutions)) {
            return;
        }

        $width = $values->getOptional(Width::class);
        $height = $values->getOptional(Height::class);
        assert((null === $width || is_int($width)) && (null === $height || is_int($height)));
        $dimensions = [];

        if (null !== $width) {
            $dimensions[] = $width . 'x';
        }

        if (null !== $height) {
            $dimensions[] = 'x' . $height;
        }

        if (null !== $width && null !== $height) {
            $dimensions[] = $width . 'x' . $height;
        }

        if (empty($dimensions)) {
            return;
        }

        if (0 >= count(array_intersect($allowedResolutions, $dimensions))) {
            throw new ModifierException(sprintf(
                'Invalid combination of width and height modifiers, %s is not supported.',
                $width . 'x' . $height,
            ));
        }
    }
}
