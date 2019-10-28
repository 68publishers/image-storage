<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Cleaner;

use Nette;

final class FileKeep
{
	use Nette\StaticClass;

	/** @var array  */
	public static $files = [
		'.gitignore',
		'.gitkeep',
	];

	/**
	 * @param string $filename
	 *
	 * @return bool
	 */
	public static function isKept(string $filename): bool
	{
		return in_array($filename, static::$files, TRUE);
	}
}
