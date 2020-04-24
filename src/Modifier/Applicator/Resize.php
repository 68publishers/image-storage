<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Nette;
use Intervention;
use SixtyEightPublishers;

final class Resize implements IModifierApplicator
{
	use Nette\SmartObject;

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator **************/

	/**
	 * {@inheritdoc}
	 */
	public function apply(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): Intervention\Image\Image
	{
		$width = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Width::class);
		$height = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Height::class);
		$aspectRatio = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\AspectRatio::class, []);
		$pd = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\PixelDensity::class, 1.0);
		$fit = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Fit::class, SixtyEightPublishers\ImageStorage\Modifier\Fit::CROP_CENTER);

		if (!empty($aspectRatio) && ((NULL === $width && NULL === $height) || (NULL !== $width && NULL !== $height))) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
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
			$width = $height * (($aspectRatio[SixtyEightPublishers\ImageStorage\Modifier\AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[SixtyEightPublishers\ImageStorage\Modifier\AspectRatio::KEY_HEIGHT] ?? $imageHeight));
		} elseif (NULL === $height) {
			$height = $width / (($aspectRatio[SixtyEightPublishers\ImageStorage\Modifier\AspectRatio::KEY_WIDTH] ?? $imageWidth) / ($aspectRatio[SixtyEightPublishers\ImageStorage\Modifier\AspectRatio::KEY_HEIGHT] ?? $imageHeight));
		}

		// apply pixel density
		$width = (int) ($width * $pd);
		$height = (int) ($height * $pd);

		if ($width === $imageWidth && $height === $imageHeight) {
			return $image;
		}

		switch ($fit) {
			case SixtyEightPublishers\ImageStorage\Modifier\Fit::CONTAIN:
				return $image->resize($width, $height, static function (Intervention\Image\Constraint $constraint) {
					$constraint->aspectRatio();
				});

			case SixtyEightPublishers\ImageStorage\Modifier\Fit::STRETCH:
				return $image->resize($width, $height);

			case SixtyEightPublishers\ImageStorage\Modifier\Fit::FILL:
				return $image->resize($width, $height, static function (Intervention\Image\Constraint $constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				})->resizeCanvas($width, $height, 'center');
		}

		if (Nette\Utils\Strings::startsWith($fit, 'crop-')) {
			$fit = Nette\Utils\Strings::substring($fit, 5);
		}

		return $image->fit($width, $height, NULL, $fit);
	}
}
