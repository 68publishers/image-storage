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

final class ResourceFactory implements ResourceFactoryInterface
{
	/** @var \League\Flysystem\FilesystemReader  */
	private $filesystemReader;

	/** @var \Intervention\Image\ImageManager  */
	private $imageManager;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/**
	 * @param \League\Flysystem\FilesystemReader                                         $filesystemReader
	 * @param \Intervention\Image\ImageManager                                           $imageManager
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 */
	public function __construct(FilesystemReader $filesystemReader, ImageManager $imageManager, ModifierFacadeInterface $modifierFacade)
	{
		$this->filesystemReader = $filesystemReader;
		$this->imageManager = $imageManager;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \League\Flysystem\FilesystemException
	 */
	public function createResource(PathInfoInterface $pathInfo): ResourceInterface
	{
		if ($pathInfo instanceof ImagePathInfoInterface && NULL !== $pathInfo->getModifiers()) {
			$sourcePathInfo = $pathInfo->withModifiers(NULL);
		}

		$path = isset($sourcePathInfo) ? $sourcePathInfo->getPath() : $pathInfo->getPath();
		$filesystemPath = ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE . $path;

		if (FALSE === $this->filesystemReader->fileExists($filesystemPath)) {
			throw new FileNotFoundException($path);
		}

		try {
			$source = $this->filesystemReader->read($filesystemPath);
		} catch (UnableToReadFile $e) {
			throw new FilesystemException(sprintf(
				'Unable to read file "%s"',
				$path
			), 0, $e);
		} catch (LeagueFilesystemException $e) {
			throw new FilesystemException($e->getMessage(), 0, $e);
		}

		$tmpFilename = tempnam(sys_get_temp_dir(), '68Publishers_ImageStorage');

		if (FALSE === file_put_contents($tmpFilename, $source)) {
			throw new FilesystemException(sprintf(
				'Unable to write tmp file for "%s"',
				$path
			));
		}

		return new TmpFileResource($pathInfo, $this->imageManager->make($tmpFilename), $this->modifierFacade, new TmpFile($tmpFilename));
	}

	/**
	 * {@inheritdoc}
	 */
	public function createResourceFromLocalFile(PathInfoInterface $pathInfo, string $filename): ResourceInterface
	{
		return new Resource($pathInfo, $this->imageManager->make($filename), $this->modifierFacade);
	}
}
