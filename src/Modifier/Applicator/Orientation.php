<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Orientation as OrientationModifier;

final class Orientation implements ModifierApplicatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image
	{
		$orientation = $values->getOptional(OrientationModifier::class);

		if (NULL === $orientation) {
			return $image;
		}

		return ($orientation === 'auto') ? $image->orientate() : $image->rotate((float) $orientation);
	}
}
