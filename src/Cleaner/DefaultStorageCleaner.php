<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Cleaner;

use Nette;
use League;
use SixtyEightPublishers;

final class DefaultStorageCleaner implements IStorageCleaner
{
	use Nette\SmartObject;

	/** @var string  */
	private $name;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $filesystem;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \League\Flysystem\Config  */
	private $config;

	/** @var array  */
	private $listContentsCache = [];

	/**
	 * @param string                                        $name
	 * @param \League\Flysystem\FilesystemInterface         $filesystem
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env $env
	 */
	public function __construct(
		string $name,
		League\Flysystem\FilesystemInterface $filesystem,
		SixtyEightPublishers\ImageStorage\Config\Env $env
	) {
		$this->name = $name;
		$this->filesystem = $filesystem;
		$this->env = $env;
		$this->config = $filesystem instanceof League\Flysystem\Filesystem ? $filesystem->getConfig() : new League\Flysystem\Config();
	}

	/**
	 * @param string $namespace
	 * @param bool   $recursive
	 *
	 * @return array
	 */
	private function listContents(string $namespace, bool $recursive): array
	{
		$key = $namespace . '_' . ($recursive ? '1' : '0');

		if (!isset($this->listContentsCache[$key])) {
			$this->listContentsCache[$key] = array_filter($this->filesystem->listContents($namespace, $recursive), static function (array $content) {
				return 'file' !== $content['type'] || !FileKeep::isKept($content['basename']);
			});
		}

		return $this->listContentsCache[$key];
	}

	/**
	 * @param array $contents
	 *
	 * @return void
	 */
	private function deleteAll(array $contents): void
	{
		foreach ($contents as $content) {
			if ('dir' === $content['type']) {
				$this->filesystem->deleteDir($content['path']);
			}

			if ('file' === $content['type']) {
				try {
					$this->filesystem->delete($content['path']);
				} catch (League\Flysystem\FileNotFoundException $e) {
					# nothing, just removed
				}
			}
		}
	}

	/**
	 * @param array $contents
	 *
	 * @return void
	 */
	private function deleteCacheOnly(array $contents): void
	{
		$paths = [];
		$regex = $this->createOriginalRegex();

		foreach ($contents as $content) {
			if ('file' !== $content['type']) {
				continue;
			}

			if (FALSE === isset($path[$content['dirname']]) && FALSE === (bool) preg_match($regex, $content['dirname'])) {
				$paths[$content['dirname']] = TRUE;
			}
		}

		foreach (array_keys($paths) as $path) {
			$this->filesystem->deleteDir($path);
		}
	}

	/**
	 * @return string
	 */
	private function createOriginalRegex(): string
	{
		return sprintf('/^.*(%s)$/', preg_quote($this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER], '/'));
	}

	/*************** interface \SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount(?string $namespace, bool $cacheOnly = FALSE): int
	{
		$contents = $this->listContents($namespace ?? '', TRUE);

		$regex = TRUE === $cacheOnly ? $this->createOriginalRegex() : NULL;

		$contents = array_filter($contents, static function (array $metadata) use ($regex) {
			if ('file' !== $metadata['type']) {
				return FALSE;
			}

			return NULL === $regex || FALSE === (bool) preg_match($regex, $metadata['dirname']);
		});

		return count($contents);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean(?string $namespace = NULL, bool $cacheOnly = FALSE): void
	{
		$contents = $this->listContents($namespace ?? '', $cacheOnly);
		$disableAsserts = $this->config->get('disable_asserts', NULL);

		$this->config->set('disable_asserts', TRUE);

		if (FALSE === $cacheOnly) {
			$this->deleteAll($contents);
		} else {
			$this->deleteCacheOnly($contents);
		}

		$this->config->set('disable_asserts', $disableAsserts);
	}
}
