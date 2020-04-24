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

		// resize image & crop it to the center
		// @TODO: Implement crop options in future!

		[ $resizeWidth, $resizeHeight ] = ($height > $width * ($imageHeight / $imageWidth))
			? [ $height * ($imageWidth / $imageHeight), $height ]
			: [ $width, $width * ($imageHeight / $imageWidth) ];

		$image->resize($resizeWidth, $resizeHeight, static function (Intervention\Image\Constraint $constraint) {
			$constraint->aspectRatio();
		});

		$imageWidth = $image->width();
		$imageHeight = $image->height();
		$offsetX = 0 > ($offsetX = (int) (($imageWidth * 50 / 100) - ($width / 2))) ? 0 : $offsetX;
		$offsetY = 0 > ($offsetY = (int) (($imageHeight * 50 / 100) - ($height / 2))) ? 0 : $offsetY;

		return $image->crop(
			$width,
			$height,
			$offsetX > ($imageWidth - $width) ? ($imageWidth - $width) : $offsetX,
			$offsetY > ($imageHeight - $height) ? ($imageHeight - $height) : $offsetY
		);
	}
}
