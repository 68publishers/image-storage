<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers;

interface INoImageProvider
{
	/**
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function getNoImage(?string $name = NULL): SixtyEightPublishers\ImageStorage\ImageInfo;

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isNoImage(string $path): bool;
}
