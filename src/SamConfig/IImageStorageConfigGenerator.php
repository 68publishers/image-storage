<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\SamConfig;

use SixtyEightPublishers;

interface IImageStorageConfigGenerator
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage
	 * @param array                                            $properties
	 * @param string                                           $outputPath
	 *
	 * @return void
	 */
	public function generate(SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage, array $properties, string $outputPath): void;
}
