<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Nette;
use Intervention;
use SixtyEightPublishers;

final class Format implements IModifierApplicator
{
	use Nette\SmartObject;

	public const DEFAULT_FORMAT = 'jpg';

	public const SUPPORTED_FORMATS = [
		'gif' => 'image/gif',
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'webp' => 'image/webp',
	];

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env $env
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Env $env)
	{
		$this->env = $env;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator **************/

	/**
	 * {@inheritdoc}
	 */
	public function apply(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): Intervention\Image\Image
	{
		$preserveFormat = TRUE === $info->isNoImage() ?: $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\PreserveFormat::class, FALSE);
		$quality = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Quality::class, $this->env[SixtyEightPublishers\ImageStorage\Config\Env::RESIZE_QUALITY]);

		# nothing
		if (TRUE === $preserveFormat && 100 === $quality) {
			return $image;
		}

		$imageFormat = array_search($image->mime(), self::SUPPORTED_FORMATS, TRUE);
		$newFormat = (TRUE === $preserveFormat && $imageFormat) ? $imageFormat : self::DEFAULT_FORMAT;

		# same format
		if ($imageFormat === $newFormat) {
			return $image->encode($newFormat, $quality);
		}

		# change format to jpg
		if ('jpg' === $newFormat) {
			$image = $image->getDriver()
				->newImage($image->width(), $image->height(), '#fff')
				->insert($image, 'top-left', 0, 0);
		}

		return $image->encode($newFormat, $quality);
	}
}
