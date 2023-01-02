<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

interface ModifierApplicatorInterface
{
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image;
}
