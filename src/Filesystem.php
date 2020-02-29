<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use Nette;
use League;

final class Filesystem
{
	use Nette\SmartObject;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $source;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $cache;

	/**
	 * @param \League\Flysystem\FilesystemInterface $source
	 * @param \League\Flysystem\FilesystemInterface $cache
	 */
	public function __construct(League\Flysystem\FilesystemInterface $source, League\Flysystem\FilesystemInterface $cache)
	{
		$this->source = $source;
		$this->cache = $cache;
	}

	/**
	 * @return \League\Flysystem\FilesystemInterface
	 */
	public function getSource(): League\Flysystem\FilesystemInterface
	{
		return $this->source;
	}

	/**
	 * @return \League\Flysystem\FilesystemInterface
	 */
	public function getCache(): League\Flysystem\FilesystemInterface
	{
		return $this->cache;
	}
}
