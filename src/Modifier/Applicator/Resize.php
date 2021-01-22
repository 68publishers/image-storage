<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use Intervention\Image\Constraint;
use SixtyEightPublishers\ImageStorage\Modifier\Fit;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\AspectRatio;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class Resize implements ModifierApplicatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image
	{
		$width = $values->getOptional(Width::class);
		$height = $values->getOptional(Height::class);
		$aspectRatio = $values->getOptional(AspectRatio::class, []);
		$pd = $values->getOptional(PixelDensity::class, 1.0);
		$fit = $values->getOptional(Fit::class, Fit::CROP_CENTER);

		if (!empty($aspectRatio) && ((NULL === $width && NULL === $height) || (NULL !== $width && NULL !== $height))) {
			throw new ModifierException(sprintf(
				'The only one dimension (width or height) must be defined if an aspect ratio is used. Passed values: w=%s, h=%s, ar=%s',
				$width ?? 'null',
				$height ?? 'null',
				implode(':', $aspectRatio)
			));
		}

		if (NULL === $width && NULL === $height && 1.0 === $pd) {
			return $image;
		}

		$imageWidth = (int) $image->width();
		$imageHeight = (int) $image->height();

		// calculate width & height
		if (NULL === $width && NULL === $height) {
			$width = $imageWidth;
			$height = $imageHeight;
		} elseif (NULL === $width) {
			$width = $height * (($aspectRatio[AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[AspectRatio::KEY_HEIGHT] ?? $imageHeight));
		} elseif (NULL === $height) {
			$height = $width / (($aspectRatio[AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[AspectRatio::KEY_HEIGHT] ?? $imageHeight));
		}

		// apply pixel density
		$width = (int) ($width * $pd);
		$height = (int) ($height * $pd);

		if ($width === $imageWidth && $height === $imageHeight) {
			return $image;
		}

		switch ($fit) {
			case Fit::CONTAIN:
				return $image->resize($width, $height, static function (Constraint $constraint) {
					$constraint->aspectRatio();
				});

			case Fit::STRETCH:
				return $image->resize($width, $height);

			case Fit::FILL:
				return $image->resize($width, $height, static function (Constraint $constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				})->resizeCanvas($width, $height, 'center');
		}

		if (0 === strncmp($fit, 'crop-', 5)) {
			$fit = substr($fit, 5);
		}

		return $image->fit($width, $height, NULL, $fit);
	}
}
