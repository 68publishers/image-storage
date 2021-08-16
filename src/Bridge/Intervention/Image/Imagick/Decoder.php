<?php

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Imagick;
use Intervention\Image\Image;
use Intervention\Image\Imagick\Decoder as ImagickDecoder;

final class Decoder extends ImagickDecoder
{
	/**
	 * {@inheritDoc}
	 */
	public function initFromImagick(Imagick $object) : Image
	{
		// reset image orientation
		$object->setImageOrientation(Imagick::ORIENTATION_UNDEFINED);

		return new Image(new Driver(), $object);
	}
}
