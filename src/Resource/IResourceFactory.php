<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use SixtyEightPublishers;

interface IResourceFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Resource\IResource
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	public function createResource(SixtyEightPublishers\ImageStorage\ImageInfo $info): IResource;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param string                                       $filename
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Resource\IResource
	 */
	public function createResourceFromLocalFile(SixtyEightPublishers\ImageStorage\ImageInfo $info, string $filename): IResource;
}
