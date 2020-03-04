<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

use SixtyEightPublishers;

final class ImageInfoException extends \Exception implements IException
{
	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public static function invalidPath(string $path): self
	{
		return new static(sprintf(
			'Given path "%s" is not valid path for %s',
			$path,
			SixtyEightPublishers\ImageStorage\ImageInfo::class
		));
	}

	/**
	 * @param string $extension
	 *
	 * @return static
	 */
	public static function unsupportedExtension(string $extension): self
	{
		return new static(sprintf(
			'File extension .%s is not supported.',
			$extension
		));
	}
}
