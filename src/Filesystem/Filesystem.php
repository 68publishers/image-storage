<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\PathNormalizer;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem as LeagueFilesystem;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class Filesystem extends LeagueFilesystem implements AdapterProviderInterface
{
	/** @var \League\Flysystem\FilesystemAdapter  */
	private $adapter;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(FilesystemAdapter $adapter, array $config = [], PathNormalizer $pathNormalizer = NULL)
	{
		parent::__construct($adapter, $config, $pathNormalizer);

		$this->adapter = $adapter;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function getAdapter(?string $name = NULL): FilesystemAdapter
	{
		if (NULL !== $name) {
			throw new InvalidArgumentException('This filesystem is non-prefixed.');
		}

		return $this->adapter;
	}
}
