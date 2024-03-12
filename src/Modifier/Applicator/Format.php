<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use function assert;
use function in_array;
use function is_int;

final class Format implements ModifierApplicatorInterface
{
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image
    {
        $extension = $this->getFileExtension($image, $pathInfo);
        $quality = $values->getOptional(Quality::class, $config[Config::ENCODE_QUALITY]);
        assert(is_int($quality));

        if (in_array($extension, ['jpg', 'pjpg'], true)) {
            $image = $image->getDriver()
                ->newImage($image->width(), $image->height(), '#fff')
                ->insert($image, 'top-left', 0, 0);

            if ('pjpg' === $extension) {
                $image->interlace();
                $extension = 'jpg';
            }
        }

        return $image->encode($extension, $quality);
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
