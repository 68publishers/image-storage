<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Assets;

use Nette;
use SixtyEightPublishers;

final class Asset
{
	use Nette\SmartObject;

	/** @var \SplFileInfo  */
	private $fileInfo;

	/** @var string  */
	private $outputPath;

	/**
	 * @param \SplFileInfo $fileInfo
	 * @param string       $outputPath
	 */
	public function __construct(\SplFileInfo $fileInfo, string $outputPath)
	{
		$this->fileInfo = $fileInfo;
		$this->outputPath = $outputPath;
	}

	/**
	 * @param string $file
	 * @param string $namespace
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Assets\Asset
	 */
	public static function fromFile(string $file, string $namespace): Asset
	{
		return new static(new \SplFileInfo($file), Nette\Utils\Strings::trim($namespace, '\\/'));
	}

	/**
	 * @param string $directory
	 * @param string $namespace
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Assets\Asset[]
	 */
	public static function fromDirectory(string $directory, string $namespace): array
	{
		$namespace = Nette\Utils\Strings::trim($namespace, '\\/');
		$normalizedDirectory = str_replace('\\', '/', rtrim(realpath($directory), '\\/')) . '/';

		return array_map(static function (\SplFileInfo $fileInfo) use ($namespace, $normalizedDirectory) {
			$path = str_replace('\\', '/', $fileInfo->getRealPath());

			if (!Nette\Utils\Strings::startsWith($path, $normalizedDirectory)) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
					'Invalid paths in method %s, path "%s" must starts with "%s"',
					__METHOD__,
					$path,
					$normalizedDirectory
				));
			}

			return new static($fileInfo, sprintf(
				'%s/%s',
				$namespace,
				Nette\Utils\Strings::substring($path, Nette\Utils\Strings::length($normalizedDirectory))
			));
		}, array_values(iterator_to_array(Nette\Utils\Finder::findFiles('*')->from($directory))));
	}

	/**
	 * @return \SplFileInfo
	 */
	public function getFileInfo(): \SplFileInfo
	{
		return $this->fileInfo;
	}

	/**
	 * @return string
	 */
	public function getOutputPath(): string
	{
		return $this->outputPath;
	}
}
