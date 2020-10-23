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
	 * @param string|NULL                                              $type
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo|string|NULL $info
	 * @param array|string|NULL                                        $modifier
	 * @param string|NULL                                              $linkGeneratorName
	 *
	 * @return array
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function getSrcAttributes(?string $type, $info, $modifier, ?string $linkGeneratorName = NULL): array
	{
		[$imageStorage, $info, $originalExt] = $this->expandArguments($info, $type, $linkGeneratorName);

		$output = [
			'src' => $imageStorage->link($info, $modifier),
			'type' => $type ?? SixtyEightPublishers\ImageStorage\Helper\SupportedType::getTypeByExtension($info->getExtension()),
		];

		$info->setExtension($originalExt);

		return $output;
	}

	/**
	 * @param string|NULL                                                          $type
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo|string|NULL             $info
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param array|string|NULL                                                    $modifier
	 * @param string|NULL                                                          $linkGeneratorName
	 *
	 * @return array
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function getSrcSetAttributes(?string $type, $info, SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor, $modifier = NULL, ?string $linkGeneratorName = NULL): array
	{
		[$imageStorage, $info, $originalExt] = $this->expandArguments($info, $type, $linkGeneratorName);

		$output = [
			'src' => $imageStorage->link($info, $modifier ?? $descriptor->getDefaultModifiers()),
			'srcset' => $imageStorage->srcSet($info, $descriptor, $modifier),
			'type' => $type ?? SixtyEightPublishers\ImageStorage\Helper\SupportedType::getTypeByExtension($info->getExtension()),
		];

		$info->setExtension($originalExt);

		return $output;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo|string|NULL $info
	 * @param string|NULL                                              $type
	 * @param string|NULL                                              $linkGeneratorName
	 *
	 * @return array
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	private function expandArguments($info, ?string $type, ?string $linkGeneratorName): array
	{
		if ($info instanceof SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo) {
			$linkGeneratorName = $linkGeneratorName ?? $info->getStorageName();
		}

		$imageStorage = $this->imageStorageProvider->get($linkGeneratorName);

		if (empty($info)) {
			$info = $imageStorage->getNoImage();
		}

		$info = $info instanceof SixtyEightPublishers\ImageStorage\ImageInfo ? $info : $imageStorage->createImageInfo((string) $info);

		$originalExtension = $info->getExtension();

		if (NULL !== $type) {
			$info->setExtension(SixtyEightPublishers\ImageStorage\Helper\SupportedType::getExtensionByType($type));
		} elseif (NULL === $originalExtension) {
			$info->setExtension(SixtyEightPublishers\ImageStorage\Helper\SupportedType::getDefaultExtension());
		}

		return [
			$imageStorage,
			$info,
			$originalExtension,
		];
	}
}
