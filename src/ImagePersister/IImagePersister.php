<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImagePersister;

use SixtyEightPublishers;

interface IImagePersister
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Filesystem
	 */
	public function getFilesystem(): SixtyEightPublishers\ImageStorage\Filesystem;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array|string|NULL                            $modifiers
	 *
	 * @return bool
	 */
	public function exists(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): bool;

	/**
	 * Returns path of stored image
	 *
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 * @param array|string|NULL                                     $modifiers
	 * @param array                                                 $config
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, $modifiers = NULL, array $config = []): string;

	/**
	 * Returns path of stored image
	 *
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 * @param array                                                 $config
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	public function update(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, array $config = []): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param bool                                         $cacheOnly
	 *
	 * @return void
	 */
	public function delete(SixtyEightPublishers\ImageStorage\ImageInfo $info, bool $cacheOnly = FALSE): void;
}
