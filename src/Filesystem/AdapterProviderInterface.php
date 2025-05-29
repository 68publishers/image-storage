<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\FilesystemAdapter;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

interface AdapterProviderInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function getAdapter(?string $name = null): FilesystemAdapter;
}
