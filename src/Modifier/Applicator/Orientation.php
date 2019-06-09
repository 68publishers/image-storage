<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Nette;
use Intervention;
use SixtyEightPublishers;

final class Orientation implements IModifierApplicator
{
	use Nette\SmartObject;

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator **************/

	/**
	 * {@inheritdoc}
	 */
	public function apply(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): Intervention\Image\Image
	{
		$orientation = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Orientation::class);

		if (NULL === $orientation) {
			return $image;
		}

		return ($orientation === 'auto') ? $image->orientate() : $image->rotate((float) $orientation);
	}
}
