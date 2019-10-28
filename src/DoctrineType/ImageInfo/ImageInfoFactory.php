<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class ImageInfoFactory
{
	use Nette\StaticClass;

	/**
	 * @param string|\SixtyEightPublishers\ImageStorage\ImageInfo $imageInfo
	 * @param string|NULL                                         $imageStorageName
	 * @param string|NULL                                         $version
	 * @param bool                                                $noImageFallback
	 *
	 * @return \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public static function create($imageInfo, ?string $imageStorageName = NULL, ?string $version = NULL, $noImageFallback = FALSE): ImageInfo
	{
		$imageStorage = self::getImageStorage($imageStorageName);

		try {
			return new ImageInfo(
				$imageStorage,
				$imageInfo instanceof SixtyEightPublishers\ImageStorage\ImageInfo ? $imageInfo : $imageStorage->createImageInfo((string) $imageInfo),
				$imageStorage->getName(),
				$version
			);
		} catch (SixtyEightPublishers\ImageStorage\Exception\ImageInfoException $e) {
			if (FALSE === $noImageFallback) {
				throw $e;
			}

			return new ImageInfo(
				$imageStorage,
				$imageStorage->resolveNoImage((string) $imageInfo),
				$imageStorage->getName(),
				$version
			);
		}
	}

	/**
	 * @param string|NULL $name
	 * @param string|NULL $imageStorageName
	 *
	 * @return \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public static function createNoImage(?string $name = NULL, ?string $imageStorageName = NULL): ImageInfo
	{
		$imageStorage = self::getImageStorage($imageStorageName);

		return self::create($imageStorage->getNoImage($name), $imageStorageName);
	}

	/**
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\IImageStorage
	 * @throws \Doctrine\DBAL\DBALException
	 */
	private static function getImageStorage(?string $name): SixtyEightPublishers\ImageStorage\IImageStorage
	{
		static $provider;

		if (NULL === $provider) {
			/** @noinspection PhpUndefinedMethodInspection */
			/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider $provider */
			$provider = Doctrine\DBAL\Types\Type::getType(ImageInfoType::NAME)->getImageStorageProvider();
		}

		try {
			$imageStorage = $provider->get($name);
		} catch (SixtyEightPublishers\ImageStorage\Exception\InvalidStateException $e) {
			if (NULL === $name) {
				throw $e;
			}

			trigger_error($e->getMessage(), E_WARNING);
			$imageStorage = $provider->get();
		}

		return $imageStorage;
	}
}
