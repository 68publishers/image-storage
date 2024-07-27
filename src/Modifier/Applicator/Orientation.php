<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Orientation as OrientationModifier;
use function is_numeric;
use function is_string;

final class Orientation implements ModifierApplicatorInterface
{
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): ?Image
    {
        $orientation = $values->getOptional(OrientationModifier::class);

        if (!is_string($orientation) && !is_numeric($orientation)) {
            return null;
        }

        if ('auto' === $orientation) {
            $exifOrientation = $image->exif('Orientation');

            if (2 <= $exifOrientation && 8 >= $exifOrientation) {
                return $image->orientate();
            }

            return null;
        }

        return $image->rotate((float) $orientation);
    }
}
