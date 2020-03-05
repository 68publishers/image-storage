<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use Nette;
use SixtyEightPublishers;

final class NoImageProvider implements INoImageProvider
{
	use Nette\SmartObject;

	/** @var string|NULL  */
	private $defaultPath;

	/** @var string[]  */
	private $paths;

	/**
	 * @param string|NULL $defaultPath
	 * @param array       $paths
	 */
	public function __construct(?string $defaultPath, array $paths)
	{
		$this->defaultPath = $defaultPath;
		$this->paths = $paths;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	private function getDefaultImageInfo(): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		if (NULL === $this->defaultPath) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException('Default no-image path is not defined.');
		}

		return new SixtyEightPublishers\ImageStorage\ImageInfo($this->defaultPath);
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider ****************/

	/**
	 * {@inheritdoc}
	 */
	public function getNoImage(?string $name = NULL): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		if (NULL === $name) {
			return $this->getDefaultImageInfo();
		}

		if (!isset($this->paths[$name])) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'No-image with name "%s" is not defined.',
				$name
			));
		}

		return new SixtyEightPublishers\ImageStorage\ImageInfo($this->paths[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isNoImage(string $path): bool
	{
		return $path === $this->defaultPath || in_array($path, $this->paths, TRUE);
	}
}
