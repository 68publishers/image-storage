<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Info;

use SixtyEightPublishers\ImageStorage\FileInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;

interface InfoFactoryInterface
{
    /**
     * @param string|array<string, string|numeric|bool>|null $modifier
     */
    public function createPathInfo(string $path, string|array|null $modifier = null): PathInfoInterface;

    public function createFileInfo(PathInfoInterface $pathInfo): FileInfoInterface;
}
