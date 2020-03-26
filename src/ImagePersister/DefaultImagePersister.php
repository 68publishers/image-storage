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

	/** @var \SixtyEightPublishers\ImageStorage\Filesystem  */
	private $filesystem;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Filesystem                      $filesystem
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config                   $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Filesystem $filesystem,
		SixtyEightPublishers\ImageStorage\Config\Config $config,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->filesystem = $filesystem;
		$this->config = $config;
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

			$path = $resource->getInfo()->createCachedPath(
				$this->modifierFacade->formatAsString($modifiers)
			);
			$filesystem = $this->filesystem->getCache();
		} else {
			$path = $resource->getInfo()->createSourcePath();
			$filesystem = $this->filesystem->getSource();
		}

		try {
			$result = (bool) $cb($filesystem, $resource, $path);

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
		$image = $image->isEncoded() ? $image : $image->encode(NULL, $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ENCODE_QUALITY]);

		return $image->getEncoded();
	}

	/**
	 * @param \League\Flysystem\FilesystemInterface $filesystem
	 * @param callable                              $cb
	 *
	 * @return void
	 */
	protected function prepareDelete(League\Flysystem\FilesystemInterface $filesystem, callable $cb): void
	{
		if ($filesystem instanceof League\Flysystem\Filesystem) {
			$config = $filesystem->getConfig();
			$disableAsserts = $config->get('disable_asserts', NULL);

			$config->set('disable_asserts', TRUE);
		}

		$cb($filesystem, $config ?? NULL);

		if (isset($config, $disableAsserts)) {
			$config->set('disable_asserts', $disableAsserts);
		}
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister ****************/

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem(): SixtyEightPublishers\ImageStorage\Filesystem
	{
		return $this->filesystem;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): bool
	{
		$filesystem = empty($modifiers) ? $this->filesystem->getSource() : $this->filesystem->getCache();

		return $filesystem->has(
			empty($modifiers) ? $info->createSourcePath() : $info->createCachedPath($this->modifierFacade->formatAsString($modifiers))
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, $modifiers = NULL, array $config = []): string
	{
		return $this->persistResource(function (League\Flysystem\FilesystemInterface $filesystem, SixtyEightPublishers\ImageStorage\Resource\IResource $resource, string $path) use ($config) {
			return $filesystem->write($path, $this->encodeImage($resource->getImage()), $config);
		}, $resource, $modifiers);
	}

	/**
	 * {@inheritdoc}
	 */
	public function update(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, array $config = []): string
	{
		return $this->persistResource(function (League\Flysystem\FilesystemInterface $filesystem, SixtyEightPublishers\ImageStorage\Resource\IResource $resource, string $path) use ($config) {
			$result = $filesystem->update($path, $this->encodeImage($resource->getImage()), $config);

			$this->delete($resource->getInfo(), TRUE);

			return $result;
		}, $resource, []);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(SixtyEightPublishers\ImageStorage\ImageInfo $info, bool $cacheOnly = FALSE): void
	{
		$this->prepareDelete($this->filesystem->getCache(), static function (League\Flysystem\FilesystemInterface $filesystem) use ($info) {
			$filesystem->deleteDir($info->getNamespace());
		});

		if (TRUE === $cacheOnly) {
			return;
		}

		$this->prepareDelete($this->filesystem->getSource(), static function (League\Flysystem\FilesystemInterface $filesystem) use ($info) {
			$filesystem->delete($info->createSourcePath());
		});
	}
}
