<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Fixtures;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class TestApplicator implements ModifierApplicatorInterface
{
    public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): iterable
    {
        return [];
    }
}
