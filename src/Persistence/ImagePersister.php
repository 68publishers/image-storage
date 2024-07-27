<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Persistence;

use League\Flysystem\FilesystemException as LeagueFilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\StorageAttributes;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Resource\ResourceInterface as ImageResourceInterface;
use SixtyEightPublishers\ImageStorage\Resource\TmpFileImageResource;
use function assert;
use function is_scalar;
use function preg_match;
use function preg_quote;
use function sprintf;

final class ImagePersister implements ImagePersisterInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystemOperator,
        private readonly ConfigInterface $config,
    ) {}

    public function getFilesystem(): FilesystemOperator
    {
        return $this->filesystemOperator;
    }

    public function exists(FilePathInfoInterface $pathInfo): bool
    {
        $pathInfo = $this->assertPathInfo($pathInfo, __METHOD__);
        $prefix = null !== $pathInfo->getModifiers() ? self::FILESYSTEM_PREFIX_CACHE : self::FILESYSTEM_PREFIX_SOURCE;

        try {
            return $this->filesystemOperator->fileExists($prefix . $pathInfo->getPath());
        } catch (LeagueFilesystemException $e) {
            return false;
        }
    }

    public function save(ResourceInterface $resource, array $config = []): string
    {
        assert($resource instanceof ImageResourceInterface);
        $pathInfo = $this->assertPathInfo($resource->getPathInfo(), __METHOD__);

        if (null !== $pathInfo->getModifiers()) {
            $resource = $resource->modifyImage($pathInfo->getModifiers());

            $prefix = self::FILESYSTEM_PREFIX_CACHE;
        } else {
            $prefix = self::FILESYSTEM_PREFIX_SOURCE;
        }

        $path = $pathInfo->getPath();
        $flushCache = self::FILESYSTEM_PREFIX_SOURCE === $prefix && $this->exists($pathInfo);

        try {
            $this->filesystemOperator->write($prefix . $path, $this->encodeImage($resource), $config);

            if ($flushCache) {
                $this->delete($pathInfo, [
                    self::OPTION_DELETE_CACHE_ONLY => true,
                    self::OPTION_SUPPRESS_EXCEPTIONS => true,
                ]);
            }
        } catch (LeagueFilesystemException $e) {
            if (!($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? false)) {
                throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
            }
        } finally {
            if ($resource instanceof TmpFileImageResource) {
                $resource->unlink();
            }
        }

        return $path;
    }

    public function delete(FilePathInfoInterface $pathInfo, array $config = []): void
    {
        $pathInfo = $this->assertPathInfo($pathInfo, __METHOD__);

        # delete modification only
        if (null !== $pathInfo->getModifiers()) {
            $this->deleteFile(self::FILESYSTEM_PREFIX_CACHE . $pathInfo->getPath(), $config);

            return;
        }

        # delete all modifications
        try {
            $contents = $this->filesystemOperator->listContents(self::FILESYSTEM_PREFIX_CACHE . $pathInfo->getNamespace(), FilesystemReader::LIST_DEEP)->toArray();
            $regex = sprintf(
                '/^%s%s%s[^\/]+\/%s\.[a-zA-Z]+$/',
                preg_quote(self::FILESYSTEM_PREFIX_CACHE, '/'),
                preg_quote($pathInfo->getNamespace(), '/'),
                '' !== $pathInfo->getNamespace() ? '\/' : '',
                preg_quote($pathInfo->getName(), '/'),
            );

            foreach ($contents as $attributes) {
                assert($attributes instanceof StorageAttributes);

                if ($attributes->isFile() && preg_match($regex, $attributes->path())) {
                    $this->deleteFile($attributes->path(), $config);
                }
            }
        } catch (LeagueFilesystemException $e) {
            if (true !== ($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? false)) {
                throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
            }
        }

        # stop if cache-only option is set
        if (true === ($config[self::OPTION_DELETE_CACHE_ONLY] ?? false)) {
            return;
        }

        $this->deleteFile(self::FILESYSTEM_PREFIX_SOURCE . $pathInfo->getPath(), $config);
    }

    private function encodeImage(ImageResourceInterface $resource): string
    {
        if (!$resource->hasBeenModified()) {
            $contents = @file_get_contents($resource->getLocalFilename());

            if (false !== $contents) {
                return $contents;
            }
        }

        $quality = $this->config[Config::ENCODE_QUALITY];
        $image = $resource->getSource();
        $image = $image->isEncoded() ? $image : $image->encode('', is_scalar($quality) ? (int) $quality : 90);

        return $image->getEncoded();
    }

    /**
     * @param  array<string, mixed> $config
     * @throws FilesystemException
     */
    private function deleteFile(string $path, array $config): void
    {
        try {
            $this->filesystemOperator->delete($path);
        } catch (LeagueFilesystemException $e) {
            if (true === ($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? false)) {
                return;
            }

            throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function assertPathInfo(FilePathInfoInterface $pathInfo, string $method): ImagePathInfoInterface
    {
        if (!$pathInfo instanceof ImagePathInfoInterface) {
            throw new InvalidArgumentException(sprintf(
                'Path info passed into the method %s() must be an instance of %s.',
                $method,
                ImagePathInfoInterface::class,
            ));
        }

        return $pathInfo;
    }
}
