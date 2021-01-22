<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

interface ModifierApplicatorInterface
{
	/**
	 * @param \Intervention\Image\Image                                             $image
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface                   $pathInfo
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface              $config
	 *
	 * @return \Intervention\Image\Image
	 */
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image;
}
