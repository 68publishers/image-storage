<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImagePersister;

use League;
use SixtyEightPublishers;

interface IImagePersister
{
	/**
	 * @return \League\Flysystem\FilesystemInterface
	 */
	public function getFilesystem(): League\Flysystem\FilesystemInterface;

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
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, $modifiers = NULL, array $config = []): string;

	/**
	 * Returns path of stored image
	 *
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 * @param array                                                 $config
	 *
	 * @return string
	 */
	public function updateOriginal(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, array $config = []): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param bool                                         $cacheOnly
	 *
	 * @return void
	 */
	public function delete(SixtyEightPublishers\ImageStorage\ImageInfo $info, bool $cacheOnly = FALSE): void;
}
