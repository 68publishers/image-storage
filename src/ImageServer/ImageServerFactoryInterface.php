<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

interface ImageServerFactoryInterface
{
	public function create(ImageStorageInterface $imageStorage): ImageServerInterface;
}
