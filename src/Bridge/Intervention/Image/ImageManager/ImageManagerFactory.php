<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager;

use Intervention\Image\ImageManager;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick\Driver as SixtyEightPublishersImagickDriver;

final class ImageManagerFactory implements ImageManagerFactoryInterface
{
	public function create(array $config = []): ImageManager
	{
		if (isset($config['driver']) && ImageStorageExtension::DRIVER_68PUBLISHERS_IMAGICK === $config['driver']) {
			$config['driver'] = new SixtyEightPublishersImagickDriver();
		}

		return new ImageManager($config);
	}
}
