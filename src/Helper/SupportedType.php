<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Helper;

use Nette;
use SixtyEightPublishers;

final class SupportedType
{
	use Nette\StaticClass;

	/** @var array */
	private static $supportedTypes = [
		'gif' => 'image/gif',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'pjpg' => 'image/jpeg',
		'png' => 'image/png',
		'webp' => 'image/webp',
	];

	/**
	 * @return array
	 */
	public static function getSupportedTypes(): array
	{
		return array_values(array_unique(self::$supportedTypes));
	}

	/**
	 * @return array
	 */
	public static function getSupportedExtensions(): array
	{
		return array_keys(self::$supportedTypes);
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function isTypeSupported(string $type): bool
	{
		return in_array($type, self::$supportedTypes, TRUE);
	}

	/**
	 * @param string $extension
	 *
	 * @return bool
	 */
	public static function isExtensionSupported(string $extension): bool
	{
		return array_key_exists($extension, self::$supportedTypes);
	}

	/**
	 * @param string $extension
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public static function getTypeByExtension(string $extension): string
	{
		if (!self::isExtensionSupported($extension)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Extension .%s is not supported.',
				$extension
			));
		}

		return self::$supportedTypes[$extension];
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public static function getExtensionByType(string $type): string
	{
		if (!self::isTypeSupported($type)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Mime type %s is not supported.',
				$type
			));
		}

		return array_search($type, self::$supportedTypes, TRUE);
	}

	/**
	 * file extensions as keys and MimeTypes as values
	 *
	 * @param array $types
	 *
	 * @return void
	 */
	public static function setSupportedTypes(array $types): void
	{
		self::$supportedTypes = $types;
	}
}
