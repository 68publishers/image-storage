<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\FileInfoInterface as BaseFileInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;

interface FileInfoInterface extends BaseFileInfoInterface, PathInfoInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface $descriptor
	 *
	 * @return string
	 */
	public function srcSet(DescriptorInterface $descriptor): string;
}
