<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Fixtures;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;

final class TestApplicator implements ModifierApplicatorInterface
{
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image
	{
	}
}
