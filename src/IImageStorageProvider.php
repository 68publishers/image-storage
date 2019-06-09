<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

interface IImageStorageProvider
{
	/**
	 * Null = default
	 *
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\IImageStorage
	 */
	public function get(?string $name = NULL): IImageStorage;
}
