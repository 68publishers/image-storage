<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Nette;
use League;
use Intervention;
use SixtyEightPublishers;

final class ResourceFactory implements IResourceFactory
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $filesystem;

	/** @var \Intervention\Image\ImageManager  */
	private $imageManager;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 * @param \League\Flysystem\FilesystemInterface                              $filesystem
	 * @param \Intervention\Image\ImageManager                                   $imageManager
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		League\Flysystem\FilesystemInterface $filesystem,
		Intervention\Image\ImageManager $imageManager,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->env = $env;
		$this->filesystem = $filesystem;
		$this->imageManager = $imageManager;
		$this->modifierFacade = $modifierFacade;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory **************/

	/**
	 * {@inheritdoc}
	 */
	public function createResource(SixtyEightPublishers\ImageStorage\ImageInfo $info): IResource
	{
		$path = $info->createPath($this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER]);

		if (FALSE === $this->filesystem->has($path)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException($path);
		}

		$source = $this->filesystem->read($path);

		if (FALSE === $source) {
			throw new SixtyEightPublishers\ImageStorage\Exception\FilesystemException(sprintf(
				'Unable to read file "%s"',
				$path
			));
		}

		$tmpFilename = tempnam(sys_get_temp_dir(), '68Publishers_ImageStorage');

		if (FALSE === file_put_contents($tmpFilename, $source)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\FilesystemException(sprintf(
				'Unable to write tmp file for "%s"',
				$path
			));
		}

		return new TmpFileResource($this->imageManager->make($tmpFilename), $info, $this->modifierFacade, $tmpFilename);
	}

	/**
	 * {@inheritdoc}
	 */
	public function createResourceFromLocalFile(SixtyEightPublishers\ImageStorage\ImageInfo $info, string $filename): IResource
	{
		return new Resource($this->imageManager->make($filename), $info, $this->modifierFacade);
	}
}
