<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\FilesystemAdapter;

interface AdapterProviderInterface
{
    /**
     * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
     */
    public function getAdapter(?string $name = null): FilesystemAdapter;
}
