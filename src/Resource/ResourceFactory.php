<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use League\Flysystem\FilesystemException as LeagueFilesystemException;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use function sprintf;
use function tempnam;
use function sys_get_temp_dir;
use function file_put_contents;

final class ResourceFactory implements ResourceFactoryInterface
{
	public function __construct(
		private readonly FilesystemReader $filesystemReader,
		private readonly ImageManager $imageManager,
		private readonly ModifierFacadeInterface $modifierFacade
	) {
	}

	/**
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FileNotFoundException
	 * @throws \League\Flysystem\FilesystemException
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
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
				$path
			), 0, $e);
		} catch (LeagueFilesystemException $e) {
			throw new FilesystemException($e->getMessage(), 0, $e);
		}

		$tmpFilename = tempnam(sys_get_temp_dir(), '68Publishers_ImageStorage');

		if (false === file_put_contents($tmpFilename, $source)) {
			throw new FilesystemException(sprintf(
				'Unable to write tmp file for "%s".',
				$path
			));
		}

		return new TmpFileImageResource($pathInfo, $this->imageManager->make($tmpFilename), $this->modifierFacade, new TmpFile($tmpFilename));
	}

	public function createResourceFromLocalFile(PathInfoInterface $pathInfo, string $filename): ResourceInterface
	{
		return new ImageResource($pathInfo, $this->imageManager->make($filename), $this->modifierFacade);
	}
}
