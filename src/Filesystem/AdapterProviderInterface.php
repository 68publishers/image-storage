<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\FilesystemAdapter;

interface AdapterProviderInterface
{
	/**
	 * @param string|NULL $name
	 *
	 * @return \League\Flysystem\FilesystemAdapter
	 */
	public function getAdapter(?string $name = NULL): FilesystemAdapter;
}
