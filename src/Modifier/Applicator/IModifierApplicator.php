<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention;
use SixtyEightPublishers;

interface IModifierApplicator
{
	/**
	 * @param \Intervention\Image\Image                                             $image
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                          $info
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values
	 *
	 * @return \Intervention\Image\Image
	 */
	public function apply(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): Intervention\Image\Image;
}
