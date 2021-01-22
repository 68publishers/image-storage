<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Response;

use League\Flysystem\FilesystemReader;
use SixtyEightPublishers\ImageStorage\Config\Config;
use Nette\Application\IResponse as ApplicationResponse;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;

final class ResponseFactory implements ResponseFactoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function createImageResponse(FilesystemReader $reader, string $path, ConfigInterface $config): ApplicationResponse
	{
		return new ImageResponse($reader, $path, (int) $config[Config::CACHE_MAX_AGE]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createErrorResponse(ResponseException $e, ConfigInterface $config): ApplicationResponse
	{
		return new ErrorResponse($e);
	}
}
