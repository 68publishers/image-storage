<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager;

use Intervention\Image\ImageManager;

interface ImageManagerFactoryInterface
{
	/**
	 * @param array $config
	 *
	 * @return \Intervention\Image\ImageManager
	 */
	public function create(array $config = []): ImageManager;
}
