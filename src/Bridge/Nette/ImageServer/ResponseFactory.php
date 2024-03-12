<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer;

use League\Flysystem\FilesystemReader;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\ImageServer\ResponseFactoryInterface;
use function assert;
use function is_int;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function createImageResponse(FilesystemReader $reader, string $path, ConfigInterface $config): ImageResponse
    {
        $maxAge = $config[Config::CACHE_MAX_AGE];
        assert(is_int($maxAge));

        return new ImageResponse($reader, $path, $maxAge);
    }

    public function createErrorResponse(ResponseException $e, ConfigInterface $config): ErrorResponse
    {
        return new ErrorResponse($e);
    }
}
