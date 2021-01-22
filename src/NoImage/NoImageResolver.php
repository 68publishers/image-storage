<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class NoImageResolver implements NoImageResolverInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface  */
	private $infoFactory;

	/** @var \SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface  */
	private $noImageConfig;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface     $infoFactory
	 * @param \SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface $noImageConfig
	 */
	public function __construct(InfoFactoryInterface $infoFactory, NoImageConfigInterface $noImageConfig)
	{
		$this->infoFactory = $infoFactory;
		$this->noImageConfig = $noImageConfig;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNoImageConfig(): NoImageConfigInterface
	{
		return $this->noImageConfig;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function getNoImage(?string $name = NULL): PathInfoInterface
	{
		if (NULL === $name) {
			return $this->getDefaultPathInfo();
		}

		$paths = $this->noImageConfig->getPaths();

		if (!isset($paths[$name])) {
			throw new InvalidArgumentException(sprintf(
				'No-image with name "%s" is not defined.',
				$name
			));
		}

		return $this->infoFactory->createPathInfo($paths[$name]);
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
	 */
	public function resolveNoImage(string $path): PathInfoInterface
	{
		foreach ($this->noImageConfig->getPatterns() as $name => $regex) {
			if (preg_match('#' . $regex . '#', $path)) {
				return $this->getNoImage($name);
			}
		}

		return $this->getNoImage();
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\PathInfoInterface
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	private function getDefaultPathInfo(): PathInfoInterface
	{
		if (NULL === $this->noImageConfig->getDefaultPath()) {
			throw new InvalidArgumentException('Default no-image path is not defined.');
		}

		return $this->infoFactory->createPathInfo($this->noImageConfig->getDefaultPath());
	}
}
