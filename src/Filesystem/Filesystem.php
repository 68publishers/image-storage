<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Filesystem;

use League\Flysystem\PathNormalizer;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use League\Flysystem\UrlGeneration\TemporaryUrlGenerator;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class Filesystem extends LeagueFilesystem implements AdapterProviderInterface
{
	private FilesystemAdapter $adapter;

	/**
	 * @param array<string, mixed> $config
	 */
	public function __construct(
		FilesystemAdapter $adapter,
		array $config = [],
		PathNormalizer $pathNormalizer = null,
		?PublicUrlGenerator $publicUrlGenerator = null,
		?TemporaryUrlGenerator $temporaryUrlGenerator = null,
	) {
		parent::__construct($adapter, $config, $pathNormalizer, $publicUrlGenerator, $temporaryUrlGenerator);

		$this->adapter = $adapter;
	}

	public function getAdapter(?string $name = null): FilesystemAdapter
	{
		if (null !== $name) {
			throw new InvalidArgumentException('The filesystem is non-prefixed.');
		}

		return $this->adapter;
	}
}
