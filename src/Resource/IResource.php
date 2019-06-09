<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention;
use SixtyEightPublishers;

interface IResource
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function getInfo(): SixtyEightPublishers\ImageStorage\ImageInfo;

	/**
	 * @return \Intervention\Image\Image
	 */
	public function getImage(): Intervention\Image\Image;

	/**
	 * @param array|string $modifier
	 */
	public function modifyImage($modifier): void;
}
