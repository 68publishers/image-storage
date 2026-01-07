<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Imagick;
use ImagickException;
use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class StripMeta implements ModifierApplicatorInterface
{
    /**
     * @throws ImagickException
     */
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): iterable
    {
        if (true !== $values->getOptional('__stripMeta', false)) {
            return [];
        }

        $core = $image->getCore();

        if (!($core instanceof Imagick)) {
            return [];
        }

        $profiles = $core->getImageProfiles('icc');

        $core->stripImage();

        if (isset($profiles['icc'])) {
            $core->profileImage('icc', $profiles['icc']);
        }

        return [];
    }
}
