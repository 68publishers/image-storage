<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use Nette;
use SixtyEightPublishers;

final class NoImageResolver implements INoImageResolver
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\NoImageConfig  */
	private $noImageConfig;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\NoImageConfig $noImageConfig
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\NoImageConfig $noImageConfig)
	{
		$this->noImageConfig = $noImageConfig;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	private function getDefaultImageInfo(): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		if (NULL === $this->noImageConfig->getDefaultPath()) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException('Default no-image path is not defined.');
		}

		return new SixtyEightPublishers\ImageStorage\ImageInfo($this->noImageConfig->getDefaultPath());
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver ****************/

	/**
	 * {@inheritdoc}
	 */
	public function getNoImageConfig(): SixtyEightPublishers\ImageStorage\Config\NoImageConfig
	{
		return $this->noImageConfig;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function getNoImage(?string $name = NULL): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		if (NULL === $name) {
			return $this->getDefaultImageInfo();
		}

		$paths = $this->noImageConfig->getPaths();

		if (!isset($paths[$name])) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'No-image with name "%s" is not defined.',
				$name
			));
		}

		return new SixtyEightPublishers\ImageStorage\ImageInfo($paths[$name]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isNoImage(string $path): bool
	{
		return $path === $this->noImageConfig->getDefaultPath() || in_array($path, $this->noImageConfig->getPaths(), TRUE);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function resolveNoImage(string $path): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		foreach ($this->noImageConfig->getPatterns() as $name => $regex) {
			if (preg_match('#' . $regex . '#', $path)) {
				return $this->getNoImage($name);
			}
		}

		return $this->getNoImage();
	}
}
