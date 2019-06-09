<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DI;

interface IAssetsProvider
{
	/**
	 * In this format:
	 * ```
	 * return [
	 *   's3' => [
	 *     path/to/my/file.png: my/file
	 *     path/to/my/directory: my_directory
	 *   ],
	 * ];
	 * ```
	 *
	 * @return array
	 */
	public function provideAssets(): array;
}
