<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config;

use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\LambdaConfig;

final class ImageStorageLambdaConfig
{
	public string $output_dir;

	/** @var array<string, LambdaConfig> */
	public array $stacks;
}
