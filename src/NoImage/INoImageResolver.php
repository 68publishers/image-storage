<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers;

interface INoImageResolver
{
	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function resolveNoImage(string $path): SixtyEightPublishers\ImageStorage\ImageInfo;
}
