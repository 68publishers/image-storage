<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Imagick;
use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use function in_array;

final class Format implements ModifierApplicatorInterface
{
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): iterable
    {
        $extension = $this->getFileExtension($image, $pathInfo);
        $quality = $values->getOptional(Quality::class);
        $needEncode = null !== $quality || SupportedType::getTypeByExtension($extension) !== $image->mime();

        if (!$needEncode && 'pjpg' === $extension) {
            $core = $image->getCore();
            $needEncode = !($core instanceof Imagick) || in_array($core->getInterlaceScheme(), [Imagick::INTERLACE_UNDEFINED, Imagick::INTERLACE_NO], true);
        }

        if (!$needEncode) {
            return;
        }

        if (in_array($extension, ['jpg', 'pjpg'], true)) {
            $image = $image->getDriver()
                ->newImage($image->width(), $image->height(), '#fff')
                ->insert($image, 'top-left', 0, 0);

            if ('pjpg' === $extension) {
                $image->interlace();
                $extension = 'jpg';
            }
        }

        yield self::OutImage => $image;
        yield self::OutFormat => $extension;

        if (null !== $quality) {
            yield self::OutQuality => (int) $quality;
        }
    }

    private function getFileExtension(Image $image, PathInfoInterface $pathInfo): string
    {
        $extension = $pathInfo->getExtension();

        if (null !== $extension && SupportedType::isExtensionSupported($extension)) {
            return $extension;
        }

        try {
            $extension = SupportedType::getExtensionByType((string) $image->mime());
        } catch (InvalidArgumentException $e) {
            $extension = SupportedType::getDefaultExtension();
        }

        return $extension;
    }
}
