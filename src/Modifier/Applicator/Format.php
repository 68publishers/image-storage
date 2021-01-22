<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Applicator;

use Intervention\Image\Image;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class Format implements ModifierApplicatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function apply(Image $image, PathInfoInterface $pathInfo, ModifierValues $values, ConfigInterface $config): Image
	{
		$extension = $this->getFileExtension($image, $pathInfo);
		$quality = $values->getOptional(Quality::class, $config[Config::ENCODE_QUALITY]);

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

	/**
	 * @param \Intervention\Image\Image                           $image
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface $pathInfo
	 *
	 * @return string
	 */
	private function getFileExtension(Image $image, PathInfoInterface $pathInfo): string
	{
		$extension = $pathInfo->getExtension();

		if (NULL !== $extension && SupportedType::isExtensionSupported($extension)) {
			return $extension;
		}

		try {
			$extension = SupportedType::getExtensionByType($image->mime());
		} catch (InvalidArgumentException $e) {
			$extension = SupportedType::getDefaultExtension();
		}

		return $extension;
	}
}
