<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImagePersister;

use Nette;
use League;
use Intervention;
use SixtyEightPublishers;

class DefaultImagePersister implements IImagePersister
{
	use Nette\SmartObject;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $filesystem;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \League\Flysystem\FilesystemInterface                              $filesystem
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		League\Flysystem\FilesystemInterface $filesystem,
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->filesystem = $filesystem;
		$this->env = $env;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * @param callable                                              $cb
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 * @param array|string|NULL                                     $modifiers
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	protected function persistResource(callable $cb, SixtyEightPublishers\ImageStorage\Resource\IResource $resource, $modifiers = NULL): string
	{
		if (!empty($modifiers)) {
			$resource->modifyImage($modifiers);
		}

		$path = $resource->getInfo()->createPath(
			$this->modifierFacade->formatAsString($modifiers)
		);

		try {
			$result = (bool) $cb($resource, $path);

			if (FALSE === $result) {
				throw new SixtyEightPublishers\ImageStorage\Exception\FilesystemException(sprintf(
					'Could not write the image %s.',
					$path
				));
			}
		} catch (League\Flysystem\FileExistsException $exception) {
			# already written by another process (log?)
		} catch (League\Flysystem\Exception $e) {
			throw new SixtyEightPublishers\ImageStorage\Exception\FilesystemException($e->getMessage(), $e->getCode(), $e);
		} finally {
			if ($resource instanceof SixtyEightPublishers\ImageStorage\Resource\TmpFileResource) {
				$resource->unlink();
			}
		}

		return $path;
	}

	/**
	 * @param \Intervention\Image\Image $image
	 *
	 * @return string
	 */
	protected function encodeImage(Intervention\Image\Image $image): string
	{
		$image = $image->isEncoded() ? $image : $image->encode(NULL, 100);

		return $image->getEncoded();
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister ****************/

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem(): League\Flysystem\FilesystemInterface
	{
		return $this->filesystem;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): bool
	{
		return $this->filesystem->has(
			$info->createPath($this->modifierFacade->formatAsString($modifiers))
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, $modifiers = NULL, array $config = []): string
	{
		return $this->persistResource(function (SixtyEightPublishers\ImageStorage\Resource\IResource $resource, string $path) use ($config) {
			return $this->filesystem->write($path, $this->encodeImage($resource->getImage()));
		}, $resource, $modifiers);
	}

	/**
	 * {@inheritdoc}
	 */
	public function updateOriginal(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, array $config = []): string
	{
		return $this->persistResource(function (SixtyEightPublishers\ImageStorage\Resource\IResource $resource, string $path) use ($config) {
			$result = $this->filesystem->update($path, $this->encodeImage($resource->getImage()));

			$this->delete($resource->getInfo(), TRUE);

			return $result;
		}, $resource, []);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(SixtyEightPublishers\ImageStorage\ImageInfo $info, bool $cacheOnly = FALSE): void
	{
		if ($this->filesystem instanceof League\Flysystem\Filesystem) {
			$config = $this->filesystem->getConfig();
			$disableAsserts = $config->get('disable_asserts', NULL);

			$config->set('disable_asserts', TRUE);
		}

		foreach ($this->filesystem->listContents($info->getNamespace(), FALSE) as $metadata) {
			if ($metadata['type'] !== 'dir') {
				continue;
			}

			if (TRUE === $cacheOnly && $metadata['basename'] === $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER]) {
				continue;
			}

			try {
				$this->filesystem->delete($metadata['path'] . '/' . $info->getName());
			} catch (League\Flysystem\FileNotFoundException $e) {
				# nothing
			}
		}

		if (isset($config) && isset($disableAsserts)) {
			$config->set('disable_asserts', $disableAsserts);
		}
	}
}
