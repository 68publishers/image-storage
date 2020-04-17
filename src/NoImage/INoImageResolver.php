<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers;

interface INoImageResolver
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Config\NoImageConfig
	 */
	public function getNoImageConfig(): SixtyEightPublishers\ImageStorage\Config\NoImageConfig;

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

	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function resolveNoImage(string $path): SixtyEightPublishers\ImageStorage\ImageInfo;
}
