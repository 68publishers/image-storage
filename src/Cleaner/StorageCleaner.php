<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Cleaner;

use League\Flysystem\FilesystemOperator;
use SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem as ImageStorageFilesystem;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager as ImageStorageMountManager;

final class StorageCleaner implements StorageCleanerInterface
{
	public const OPTION_CACHE_ONLY = 'cache-only';

	/** @var \SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface  */
	private $storageCleaner;

	/**
	 * @param \SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface $storageCleaner
	 */
	public function __construct(StorageCleanerInterface $storageCleaner)
	{
		$this->storageCleaner = $storageCleaner;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount(FilesystemOperator $filesystemOperator, array $options = []): int
	{
		if (!$filesystemOperator instanceof ImageStorageFilesystem && !$filesystemOperator instanceof ImageStorageMountManager) {
			return $this->storageCleaner->getCount($filesystemOperator, $options);
		}

		$options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE;
		$count = $this->storageCleaner->getCount($filesystemOperator, $options);

		if (FALSE === ($options[self::OPTION_CACHE_ONLY] ?? FALSE)) {
			$options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE;
			$count += $this->storageCleaner->getCount($filesystemOperator, $options);
		}

		return $count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean(FilesystemOperator $filesystemOperator, array $options = []): void
	{
		if (!$filesystemOperator instanceof ImageStorageFilesystem && !$filesystemOperator instanceof ImageStorageMountManager) {
			$this->storageCleaner->clean($filesystemOperator, $options);

			return;
		}

		$options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE;

		$this->storageCleaner->clean($filesystemOperator, $options);

		if (TRUE === ($options[self::OPTION_CACHE_ONLY] ?? FALSE)) {
			return;
		}

		$options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE;

		$this->storageCleaner->clean($filesystemOperator, $options);
	}
}
