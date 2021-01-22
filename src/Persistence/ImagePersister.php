<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Persistence;

use Intervention\Image\Image;
use League\Flysystem\FilesystemOperator;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Resource\TmpFileResource;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use League\Flysystem\FilesystemException as LeagueFilesystemException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;

final class ImagePersister implements ImagePersisterInterface
{
	/** @var \League\Flysystem\FilesystemOperator  */
	private $filesystemOperator;

	/** @var \SixtyEightPublishers\FileStorage\Config\ConfigInterface  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/**
	 * @param \League\Flysystem\FilesystemOperator                                       $filesystemOperator
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface                   $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 */
	public function __construct(FilesystemOperator $filesystemOperator, ConfigInterface $config, ModifierFacadeInterface $modifierFacade)
	{
		$this->filesystemOperator = $filesystemOperator;
		$this->config = $config;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * @param \Intervention\Image\Image $image
	 *
	 * @return string
	 */
	protected function encodeImage(Image $image): string
	{
		$image = $image->isEncoded() ? $image : $image->encode(NULL, $this->config[Config::ENCODE_QUALITY]);

		return $image->getEncoded();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilesystem(): FilesystemOperator
	{
		return $this->filesystemOperator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists(FilePathInfoInterface $pathInfo): bool
	{
		$prefix = $pathInfo instanceof ImagePathInfoInterface && NULL !== $pathInfo->getModifiers() ? self::FILESYSTEM_PREFIX_CACHE : self::FILESYSTEM_PREFIX_SOURCE;

		try {
			return $this->filesystemOperator->fileExists($prefix . $pathInfo->getPath());
		} catch (LeagueFilesystemException $e) {
			return FALSE;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(ResourceInterface $resource, array $config = []): string
	{
		$source = $resource->getSource();
		$pathInfo = $resource->getPathInfo();

		if (!$source instanceof Image) {
			throw new InvalidStateException(sprintf(
				'A source must be instance of %s.',
				Image::class
			));
		}

		if ($pathInfo instanceof ImagePathInfoInterface && NULL !== $pathInfo->getModifiers()) {
			$this->modifierFacade->modifyImage($source, $pathInfo, $pathInfo->getModifiers());

			$prefix = self::FILESYSTEM_PREFIX_CACHE;
		} else {
			$prefix = self::FILESYSTEM_PREFIX_SOURCE;
		}

		$path = $pathInfo->getPath();
		$flushCache = self::FILESYSTEM_PREFIX_SOURCE === $prefix && $this->exists($pathInfo);

		try {
			$this->filesystemOperator->write($prefix . $path, $this->encodeImage($source), $config);

			if ($flushCache) {
				$this->delete($pathInfo, [
					self::OPTION_DELETE_CACHE_ONLY => TRUE,
					self::OPTION_SUPPRESS_EXCEPTIONS => TRUE,
				]);
			}

			return $path;
		} catch (LeagueFilesystemException $e) {
			if (!($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? FALSE)) {
				throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
			}
		} finally {
			if ($resource instanceof TmpFileResource) {
				$resource->unlink();
			}
		}

		return $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(FilePathInfoInterface $pathInfo, array $config = []): void
	{
		# delete modification only
		if ($pathInfo instanceof ImagePathInfoInterface && NULL !== $pathInfo->getModifiers()) {
			$this->deleteFile($pathInfo, self::FILESYSTEM_PREFIX_CACHE, $config);

			return;
		}

		# delete all modifications
		try {
			$contents = $this->filesystemOperator->listContents(self::FILESYSTEM_PREFIX_CACHE . $pathInfo->getNamespace(), FilesystemOperator::LIST_DEEP)->toArray();

			/** @var \League\Flysystem\StorageAttributes $attributes */
			foreach ($contents as $attributes) {
				if ($attributes->isFile() && preg_match('/^' . preg_quote($pathInfo->getNamespace(), '/') . '\/[^\/]+\/' . preg_quote($pathInfo->getName(), '/') . '\.[a-zA-Z]+$/', $attributes->path())) {
					$this->filesystemOperator->delete(self::FILESYSTEM_PREFIX_CACHE . $attributes->path());
				}
			}
		} catch (LeagueFilesystemException $e) {
			if (TRUE === ($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? FALSE)) {
				return;
			}

			throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
		}

		# stop if cache-only option is set
		if (TRUE === ($config[self::OPTION_DELETE_CACHE_ONLY] ?? FALSE)) {
			return;
		}

		$this->deleteFile($pathInfo, self::FILESYSTEM_PREFIX_SOURCE, $config);
	}

	/**
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface $pathInfo
	 * @param string                                              $prefix
	 * @param array                                               $config
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	private function deleteFile(FilePathInfoInterface $pathInfo, string $prefix, array $config): void
	{
		try {
			$this->filesystemOperator->delete($prefix . $pathInfo->getPath());
		} catch (LeagueFilesystemException $e) {
			if (TRUE === ($config[self::OPTION_SUPPRESS_EXCEPTIONS] ?? FALSE)) {
				return;
			}

			throw new FilesystemException($e->getMessage(), $e->getCode(), $e);
		}
	}
}
