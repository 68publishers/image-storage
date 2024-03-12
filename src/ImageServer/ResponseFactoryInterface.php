<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use League\Flysystem\FilesystemReader;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;

interface ResponseFactoryInterface
{
    public function createImageResponse(FilesystemReader $reader, string $path, ConfigInterface $config): object;

    public function createErrorResponse(ResponseException $e, ConfigInterface $config): object;
}
