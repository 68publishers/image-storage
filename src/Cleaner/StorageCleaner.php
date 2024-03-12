<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Cleaner;

use League\Flysystem\FilesystemOperator;
use SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem as ImageStorageFilesystem;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager as ImageStorageMountManager;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;

final class StorageCleaner implements StorageCleanerInterface
{
    public const OPTION_CACHE_ONLY = 'cache-only';

    public function __construct(
        private readonly StorageCleanerInterface $storageCleaner,
    ) {}

    public function getCount(FilesystemOperator $filesystemOperator, array $options = []): int
    {
        if (!$filesystemOperator instanceof ImageStorageFilesystem && !$filesystemOperator instanceof ImageStorageMountManager) {
            return $this->storageCleaner->getCount($filesystemOperator, $options);
        }

        $options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE;
        $count = $this->storageCleaner->getCount($filesystemOperator, $options);

        if (false === ($options[self::OPTION_CACHE_ONLY] ?? false)) {
            $options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE;
            $count += $this->storageCleaner->getCount($filesystemOperator, $options);
        }

        return $count;
    }

    public function clean(FilesystemOperator $filesystemOperator, array $options = []): void
    {
        if (!$filesystemOperator instanceof ImageStorageFilesystem && !$filesystemOperator instanceof ImageStorageMountManager) {
            $this->storageCleaner->clean($filesystemOperator, $options);

            return;
        }

        $options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE;

        $this->storageCleaner->clean($filesystemOperator, $options);

        if (true === ($options[self::OPTION_CACHE_ONLY] ?? false)) {
            return;
        }

        $options[self::OPTION_FILESYSTEM_PREFIX] = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE;

        $this->storageCleaner->clean($filesystemOperator, $options);
    }
}
