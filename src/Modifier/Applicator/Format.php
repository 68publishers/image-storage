<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Nette;
use Intervention;
use SixtyEightPublishers;

final class Format implements IModifierApplicator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env $env
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Env $env)
	{
		$this->env = $env;
	}

	/**
	 * @param \Intervention\Image\Image                    $image
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 *
	 * @return string
	 */
	private function getFileExtension(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info): string
	{
		$extension = $info->getExtension();

		if (NULL !== $extension && SixtyEightPublishers\ImageStorage\Helper\SupportedType::isExtensionSupported($extension)) {
			return $extension;
		}

		try {
			$extension = SixtyEightPublishers\ImageStorage\Helper\SupportedType::getExtensionByType($image->mime());
		} catch (SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException $e) {
			$extension = SixtyEightPublishers\ImageStorage\Helper\SupportedType::getDefaultExtension();
		}

		return $extension;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator **************/

	/**
	 * {@inheritdoc}
	 */
	public function apply(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): Intervention\Image\Image
	{
		$extension = $this->getFileExtension($image, $info);
		$quality = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Quality::class, $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ENCODE_QUALITY]);

		if (in_array($extension, ['jpg', 'pjpg'], TRUE)) {
			$image = $image->getDriver()
				->newImage($image->width(), $image->height(), '#fff')
				->insert($image, 'top-left', 0, 0);

			if ('pjpg' === $extension) {
				$image->interlace();
				$extension = 'jpg';
			}
		}

		return $image->encode($extension, $quality);
	}
}
