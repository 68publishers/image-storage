<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\Application;

use Nette\Application\Routers\Route;
use function trim;

final class ImageServerRoute extends Route
{
	public function __construct(?string $storageName, string $basePath)
	{
		parent::__construct('/' . trim($basePath, '/') . '/<path .+>', [
			'module' => 'ImageStorage',
			'presenter' => 'ImageServer',
			'action' => 'default',
			'__storageName' => $storageName,
		]);
	}
}
