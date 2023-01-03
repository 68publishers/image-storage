<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\Application;

use Nette\Application\Routers\Route;
use function rtrim;

final class ImageServerRoute extends Route
{
	public function __construct(?string $storageName, string $basePath)
	{
		parent::__construct(rtrim($basePath) . '/<path .+>', [
			'module' => 'ImageStorage',
			'presenter' => 'ImageServer',
			'action' => 'default',
			'__storageName' => $storageName,
		]);
	}
}
