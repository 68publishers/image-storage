<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

interface SamConfigGeneratorInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageStorageInterface $imageStorage
	 *
	 * @return bool
	 */
	public function hasStackForStorage(ImageStorageInterface $imageStorage): bool;

	/**
	 * Returns path to the generated config
	 *
	 * @param \SixtyEightPublishers\ImageStorage\ImageStorageInterface $imageStorage
	 *
	 * @return string
	 */
	public function generateForStorage(ImageStorageInterface $imageStorage): string;
}
