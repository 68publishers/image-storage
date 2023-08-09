<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Imagick;
use ImagickPixel;
use ImagickException;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Imagick\Encoder as ImagickEncoder;

final class Encoder extends ImagickEncoder
{
	/**
	 * @throws ImagickException
	 */
	protected function processWebp()
	{
		if (!Imagick::queryFormats('WEBP')) {
			throw new NotSupportedException(
				"Webp format is not supported by Imagick installation."
			);
		}

		$format = 'webp';
		$compression = Imagick::COMPRESSION_JPEG;

		$imagick = $this->image->getCore();
		assert($imagick instanceof Imagick);
		$imagick->setImageBackgroundColor(new ImagickPixel('transparent'));

		if (1 >= $imagick->getNumberImages()) {
			$imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_MERGE);
		} else {
			$imagick = $imagick->coalesceImages();
		}

		$imagick->setFormat($format);
		$imagick->setImageFormat($format);
		$imagick->setCompression($compression);
		$imagick->setImageCompression($compression);
		$imagick->setImageCompressionQuality($this->quality);

		return $imagick->getImagesBlob();
	}

	protected function processAvif(): string
	{
		if (!Imagick::queryFormats('AVIF')) {
			throw new NotSupportedException(
				"AVIF format is not supported by Imagick installation."
			);
		}

		$format = 'avif';
		$compression = Imagick::COMPRESSION_UNDEFINED;

		$imagick = $this->image->getCore();

		if (1 < $imagick->getNumberImages()) {
			$imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_MERGE);
		}

		$imagick->setFormat($format);
		$imagick->setImageFormat($format);
		$imagick->setCompression($compression);
		$imagick->setImageCompression($compression);
		$imagick->setCompressionQuality($this->quality);
		$imagick->setImageCompressionQuality($this->quality);

		return $imagick->getImagesBlob();
	}
}
