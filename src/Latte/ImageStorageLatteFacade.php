<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Latte;

use Nette;
use SixtyEightPublishers;

final class ImageStorageLatteFacade
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider)
	{
		$this->imageStorageProvider = $imageStorageProvider;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo|string|NULL $info
	 * @param array|string|NULL                                        $modifier
	 * @param string|NULL                                              $linkGeneratorName
	 *
	 * @return string
	 */
	public function link($info, $modifier = NULL, ?string $linkGeneratorName = NULL): string
	{
		if ($info instanceof SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo) {
			return $info->link($modifier);
		}

		$imageStorage = $this->imageStorageProvider->get($linkGeneratorName);

		if (empty($info)) {
			$info = $imageStorage->getNoImage();
		}

		return $imageStorage->link(
			$info instanceof SixtyEightPublishers\ImageStorage\ImageInfo ? $info : $imageStorage->createImageInfo((string) $info),
			$modifier
		);
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo|string|NULL             $info
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param array|string|NULL                                                    $modifier
	 * @param string|NULL                                                          $linkGeneratorName
	 *
	 * @return string
	 */
	public function srcSet($info, SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor, $modifier = NULL, ?string $linkGeneratorName = NULL): string
	{
		if ($info instanceof SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo) {
			return $info->srcSet($descriptor, $modifier);
		}

		$imageStorage = $this->imageStorageProvider->get($linkGeneratorName);

		if (empty($info)) {
			$info = $imageStorage->getNoImage();
		}

		return $imageStorage->srcSet(
			$info instanceof SixtyEightPublishers\ImageStorage\ImageInfo ? $info : $imageStorage->createImageInfo((string) $info),
			$descriptor,
			$modifier
		);
	}
}
