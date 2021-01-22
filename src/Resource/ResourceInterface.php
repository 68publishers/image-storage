<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface as FileResourceInterface;

interface ResourceInterface extends FileResourceInterface
{
	/**
	 * @return \Intervention\Image\Image
	 */
	public function getSource(): Image;

	/**
	 * @param string|array $modifiers
	 *
	 * @return void
	 */
	public function modifyImage($modifiers): void;
}
