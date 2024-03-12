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
use function file_put_contents;
use function sprintf;
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

        $tmpFilename = (string) tempnam(sys_get_temp_dir(), '68Publishers_ImageStorage');

        if (false === file_put_contents($tmpFilename, $source)) {
            throw new FilesystemException(sprintf(
                'Unable to write tmp file for "%s".',
                $path,
            ));
        }

        return new TmpFileImageResource(
            pathInfo: $pathInfo,
            image: $this->makeImage(
                source: $tmpFilename,
                location: $path,
            ),
            modifierFacade: $this->modifierFacade,
            tmpFile: new TmpFile($tmpFilename),
        );
    }

    public function createResourceFromFile(PathInfoInterface $pathInfo, string $filename): ResourceInterface
    {
        return new ImageResource(
            pathInfo: $pathInfo,
            image: $this->makeImage(
                source: $filename,
                location: $filename,
            ),
            modifierFacade: $this->modifierFacade,
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
