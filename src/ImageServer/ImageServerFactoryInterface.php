<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

interface ImageServerFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageStorageInterface $imageStorage
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface
	 */
	public function create(ImageStorageInterface $imageStorage): ImageServerInterface;
}
