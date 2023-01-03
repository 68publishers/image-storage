<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class ImageStorageConfig
{
	public string|Statement $driver;

	/** @var array<string, StorageConfig> */
	public array $storages;
}
