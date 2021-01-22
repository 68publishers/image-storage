<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Info;

use SixtyEightPublishers\ImageStorage\FileInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;

interface InfoFactoryInterface
{
	/**
	 * @param string            $path
	 * @param array|string|NULL $modifier
	 *
	 * @return \SixtyEightPublishers\ImageStorage\PathInfoInterface
	 */
	public function createPathInfo(string $path, $modifier = NULL): PathInfoInterface;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\PathInfoInterface $pathInfo
	 *
	 * @return \SixtyEightPublishers\ImageStorage\FileInfoInterface
	 */
	public function createFileInfo(PathInfoInterface $pathInfo): FileInfoInterface;
}
