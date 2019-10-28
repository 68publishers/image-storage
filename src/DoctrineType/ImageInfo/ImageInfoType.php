<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo;

use Nette;
use Doctrine;
use SixtyEightPublishers;

class ImageInfoType extends Doctrine\DBAL\Types\JsonType
{
	public const NAME = 'image_info';

	/** @var NULL|\SixtyEightPublishers\ImageStorage\IImageStorageProvider */
	private $imageStorageProvider;

	/**
	 * @internal
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider
	 *
	 * @return void
	 */
	public function setDependencies(SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider): void
	{
		$this->imageStorageProvider = $imageStorageProvider;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\IImageStorageProvider
	 */
	public function getImageStorageProvider(): SixtyEightPublishers\ImageStorage\IImageStorageProvider
	{
		if (NULL === $this->imageStorageProvider) {
			throw SixtyEightPublishers\ImageStorage\Exception\InvalidStateException::missingMethodCall(__CLASS__, 'setDependencies');
		}

		return $this->imageStorageProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToDatabaseValue($value, Doctrine\DBAL\Platforms\AbstractPlatform $platform): ?string
	{
		try {
			if (!$value instanceof ImageInfo) {
				$value = ($value instanceof SixtyEightPublishers\ImageStorage\ImageInfo || (is_string($value) && !empty($value)))
					? ImageInfoFactory::create((string) $value, NULL, is_string($value) ? NULL : $value->getVersion())
					: NULL;
			}

			return parent::convertToDatabaseValue($value, $platform);
		} catch (Nette\Utils\AssertionException $e) {
			throw SixtyEightPublishers\ImageStorage\Exception\InvalidStateException::from($e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToPHPValue($value, Doctrine\DBAL\Platforms\AbstractPlatform $platform): ?ImageInfo
	{
		$value = parent::convertToPHPValue($value, $platform);

		if (NULL === $value) {
			return NULL;
		}

		return ImageInfoFactory::create($value['path'] ?? '', $value['generator'] ?? NULL, $value['version'] ?? NULL, TRUE);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return self::NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function requiresSQLCommentHint(Doctrine\DBAL\Platforms\AbstractPlatform $platform)
	{
		TRUE;
	}
}
