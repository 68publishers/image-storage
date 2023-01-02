<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\MountManager as LeagueMountManager;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use function sprintf;

final class MountManager extends LeagueMountManager implements AdapterProviderInterface
{
	/** @var array<string, AdapterProviderInterface> */
	private array $filesystems = [];

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

	public function getAdapter(?string $name = null): FilesystemAdapter
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
