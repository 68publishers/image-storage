<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

interface SamConfigGeneratorInterface
{
	public function canGenerate(ImageStorageInterface $imageStorage): bool;

	/**
	 * Returns path to the generated config
	 */
	public function generate(ImageStorageInterface $imageStorage): string;
}
