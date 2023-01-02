<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

interface SamConfigGeneratorInterface
{
	public function hasStackForStorage(ImageStorageInterface $imageStorage): bool;

	/**
	 * Returns path to the generated config
	 */
	public function generateForStorage(ImageStorageInterface $imageStorage): string;
}
