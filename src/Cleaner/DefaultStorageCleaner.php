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

	/** @var \SixtyEightPublishers\ImageStorage\Filesystem  */
	private $filesystem;

	/**
	 * @param string                                        $name
	 * @param \SixtyEightPublishers\ImageStorage\Filesystem $filesystem
	 */
	public function __construct(string $name, SixtyEightPublishers\ImageStorage\Filesystem $filesystem)
	{
		$this->name = $name;
		$this->filesystem = $filesystem;
	}

	/**
	 * @param \League\Flysystem\FilesystemInterface $filesystem
	 * @param string|NULL                           $namespace
	 *
	 * @return int
	 */
	private function getFilesCount(League\Flysystem\FilesystemInterface $filesystem, ?string $namespace): int
	{
		$contents = array_filter($filesystem->listContents($namespace ?? '', TRUE), static function (array $content) {
			return 'file' === $content['type'] && !FileKeep::isKept($content['basename']);
		});

		return count($contents);
	}

	/**
	 * @param \League\Flysystem\FilesystemInterface $filesystem
	 * @param string|NULL                           $namespace
	 *
	 * @return void
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	private function cleanFilesystem(League\Flysystem\FilesystemInterface $filesystem, ?string $namespace): void
	{
		if ($filesystem instanceof League\Flysystem\Filesystem) {
			$config = $filesystem->getConfig();
			$disableAsserts = $config->get('disable_asserts', NULL);

			$config->set('disable_asserts', TRUE);
		}

		# delete directories and files from root
		$contents = array_filter($filesystem->listContents($namespace ?? '', FALSE), static function (array $content) {
			return 'file' !== $content['type'] || !FileKeep::isKept($content['basename']);
		});

		foreach ($contents as $content) {
			if ('dir' === $content['type']) {
				$filesystem->deleteDir($content['path']);
			}

			if ('file' === $content['type']) {
				/** @noinspection PhpUnhandledExceptionInspection */
				$filesystem->delete($content['path']);
			}
		}

		if (isset($config, $disableAsserts)) {
			$config->set('disable_asserts', $disableAsserts);
		}
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
		$count = $this->getFilesCount($this->filesystem->getCache(), $namespace);

		if (FALSE === $cacheOnly) {
			$count += $this->getFilesCount($this->filesystem->getSource(), $namespace);
		}

		return $count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean(?string $namespace = NULL, bool $cacheOnly = FALSE): void
	{
		$this->cleanFilesystem($this->filesystem->getCache(), $namespace);

		if (TRUE === $cacheOnly) {
			return;
		}

		$this->cleanFilesystem($this->filesystem->getSource(), $namespace);
	}
}
