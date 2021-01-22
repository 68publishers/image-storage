<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;

interface NoImageResolverInterface
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface
	 */
	public function getNoImageConfig(): NoImageConfigInterface;

	/**
	 * @param string|NULL $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\PathInfoInterface
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function getNoImage(?string $name = NULL): PathInfoInterface;

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isNoImage(string $path): bool;

	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\PathInfoInterface
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function resolveNoImage(string $path): PathInfoInterface;
}
