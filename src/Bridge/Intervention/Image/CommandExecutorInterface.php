<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;

interface CommandExecutorInterface
{
	/**
	 * @param array<int, mixed> $arguments
	 */
	public function execute(Image $image, string $name, array $arguments): AbstractCommand;
}
