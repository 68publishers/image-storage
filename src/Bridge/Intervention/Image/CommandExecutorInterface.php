<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;

interface CommandExecutorInterface
{
	/**
	 * @param \Intervention\Image\Image $image
	 * @param string                    $name
	 * @param array                     $arguments
	 *
	 * @return \Intervention\Image\Commands\AbstractCommand
	 */
	public function execute(Image $image, string $name, array $arguments): AbstractCommand;
}
