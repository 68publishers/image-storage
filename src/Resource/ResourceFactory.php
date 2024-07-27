<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Exception\ImageException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemException as LeagueFilesystemException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use function error_clear_last;
use function error_get_last;
use function file_put_contents;
use function filter_var;
use function fopen;
use function sprintf;
use function stream_context_create;
use function sys_get_temp_dir;
use function tempnam;

final class ResourceFactory implements ResourceFactoryInterface
{
    public function __construct(
        private readonly FilesystemReader $filesystemReader,
        private readonly ImageManager $imageManager,
        private readonly ModifierFacadeInterface $modifierFacade,
    ) {}

    /**
     * @throws FileNotFoundException
     * @throws LeagueFilesystemException
     * @throws FilesystemException
     */
    public function createResource(PathInfoInterface $pathInfo): ResourceInterface
    {
        if ($pathInfo instanceof ImagePathInfoInterface && null !== $pathInfo->getModifiers()) {
            $sourcePathInfo = $pathInfo->withModifiers(null);
        }

        $path = isset($sourcePathInfo) ? $sourcePathInfo->getPath() : $pathInfo->getPath();
        $filesystemPath = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE . $path;

        if (false === $this->filesystemReader->fileExists($filesystemPath)) {
            throw new FileNotFoundException($path);
        }

        try {
            $source = $this->filesystemReader->read($filesystemPath);
        } catch (UnableToReadFile $e) {
            throw new FilesystemException(sprintf(
                'Unable to read file "%s".',
                $path,
            ), 0, $e);
        } catch (LeagueFilesystemException $e) {
            throw new FilesystemException($e->getMessage(), 0, $e);
        }

        return $this->createTmpFileResource(
            pathInfo: $pathInfo,
            location: $path,
            source: $source,
        );
    }

    public function createResourceFromFile(PathInfoInterface $pathInfo, string $filename): ResourceInterface
    {
        if (!filter_var($filename, FILTER_VALIDATE_URL)) {
            return new ImageResource(
                pathInfo: $pathInfo,
                image: $this->makeImage(
                    source: $filename,
                    location: $filename,
                ),
                localFilename: $filename,
                modifierFacade: $this->modifierFacade,
            );
        }

        error_clear_last();

        $context = stream_context_create(
            options: [
                'http' => [
                    'method' => 'GET',
                    'protocol_version' => 1.1,
                    'header' => "Accept-language: en\r\n" . "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.71 Safari/537.36\r\n",
                ],
            ],
        );

        $source = @fopen(
            filename: $filename,
            mode: 'rb',
            context: $context,
        );

        if (false === $source) {
            throw new FilesystemException(
                message: sprintf(
                    'Can not read stream from url "%s". %s',
                    $filename,
                    error_get_last()['message'] ?? '',
                ),
            );
        }

        return $this->createTmpFileResource(
            pathInfo: $pathInfo,
            location: $filename,
            source: $source,
        );
    }

    /**
     * @throws FilesystemException
     */
    private function createTmpFileResource(PathInfoInterface $pathInfo, string $location, mixed $source): TmpFileImageResource
    {
        $tmpFilename = (string) tempnam(sys_get_temp_dir(), '68Publishers_ImageStorage');

        if (false === file_put_contents($tmpFilename, $source)) {
            throw new FilesystemException(sprintf(
                'Unable to write tmp file for "%s".',
                $location,
            ));
        }

        return new TmpFileImageResource(
            pathInfo: $pathInfo,
            image: $this->makeImage(
                source: $tmpFilename,
                location: $location,
            ),
            modifierFacade: $this->modifierFacade,
            tmpFile: new TmpFile($tmpFilename),
        );
    }

    /**
     * @throws FilesystemException
     */
    private function makeImage(mixed $source, string $location): Image
    {
        try {
            return $this->imageManager->make($source);
        } catch (ImageException $e) {
            throw new FilesystemException(
                message: sprintf(
                    'Unable to create image "%s". %s',
                    $location,
                    $e->getMessage(),
                ),
                previous: $e,
            );
        }
    }
}
