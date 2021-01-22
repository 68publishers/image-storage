<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer\Response;

use League\Flysystem\FilesystemReader;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;

interface ResponseFactoryInterface
{
	/**
	 * @param \League\Flysystem\FilesystemReader                       $reader
	 * @param string                                                   $path
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface $config
	 *
	 * @return object
	 */
	public function createImageResponse(FilesystemReader $reader, string $path, ConfigInterface $config);

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Exception\ResponseException $e
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface       $config
	 *
	 * @return object
	 */
	public function createErrorResponse(ResponseException $e, ConfigInterface $config);
}
