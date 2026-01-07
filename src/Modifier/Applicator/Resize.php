<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\AspectRatio;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Fit;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use function assert;
use function implode;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;
use function strncmp;
use function substr;

final class Resize implements ModifierApplicatorInterface
{
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): iterable
    {
        $width = $values->getOptional(Width::class);
        $height = $values->getOptional(Height::class);
        $aspectRatio = $values->getOptional(AspectRatio::class, []);
        $pd = $values->getOptional(PixelDensity::class, 1.0);
        $fit = $values->getOptional(Fit::class, Fit::CROP_CENTER);

        assert(
            (null === $width || is_int($width))
            && (null === $height || is_int($height))
            && is_array($aspectRatio)
            && is_float($pd)
            && is_string($fit),
        );

        if (!empty($aspectRatio) && ((null === $width && null === $height) || (null !== $width && null !== $height))) {
            throw new ModifierException(sprintf(
                'The only one dimension (width or height) must be defined if an aspect ratio is used. Passed values: w=%s, h=%s, ar=%s.',
                $width ?? 'null',
                $height ?? 'null',
                implode('x', $aspectRatio),
            ));
        }

        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // calculate width & height
        if (null === $width && null === $height) {
            $width = $imageWidth;
            $height = $imageHeight;
        } elseif (null === $width) {
            $width = $height * (($aspectRatio[AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[AspectRatio::KEY_HEIGHT] ?? $imageHeight));
        } elseif (null === $height) {
            $height = $width / (($aspectRatio[AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[AspectRatio::KEY_HEIGHT] ?? $imageHeight));
        }

        // apply pixel density
        $width = (int) ($width * $pd);
        $height = (int) ($height * $pd);

        if ($width === $imageWidth && $height === $imageHeight) {
            return;
        }

        yield self::OutImage => match ($fit) {
            Fit::CONTAIN => $image->resize($width, $height, static function (Constraint $constraint) {
                $constraint->aspectRatio();
            }),
            Fit::STRETCH => $image->resize($width, $height),
            Fit::FILL => $image->resize($width, $height, static function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->resizeCanvas($width, $height, 'center'),
            default => $image->fit(
                $width,
                $height,
                null,
                0 === strncmp($fit, 'crop-', 5) ? substr($fit, 5) : $fit,
            ),
        };
    }
}
