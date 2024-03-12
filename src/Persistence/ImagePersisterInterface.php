<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Persistence;

use SixtyEightPublishers\FileStorage\Persistence\FilePersisterInterface;

interface ImagePersisterInterface extends FilePersisterInterface
{
    public const FILESYSTEM_NAME_SOURCE = 'source';
    public const FILESYSTEM_NAME_CACHE = 'cache';

    public const FILESYSTEM_PREFIX_SOURCE = self::FILESYSTEM_NAME_SOURCE . '://';
    public const FILESYSTEM_PREFIX_CACHE = self::FILESYSTEM_NAME_CACHE . '://';

    public const OPTION_DELETE_CACHE_ONLY = 'delete.cache_only';
}
