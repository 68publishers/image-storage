<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\MountManager as LeagueMountManager;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class MountManager extends LeagueMountManager implements AdapterProviderInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Filesystem\AdapterProviderInterface[] */
	private $filesystems = [];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $filesystems = [])
	{
		parent::__construct($filesystems);

		foreach ($filesystems as $name => $filesystem) {
			if ($filesystem instanceof AdapterProviderInterface) {
				$this->filesystems[$name] = $filesystem;
			}
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function getAdapter(?string $name = NULL): FilesystemAdapter
	{
		if (!isset($this->filesystems[$name])) {
			throw new InvalidArgumentException(sprintf(
				'Adapter with prefix %s:// not found.',
				$name
			));
		}

		return $this->filesystems[$name]->getAdapter();
	}
}
