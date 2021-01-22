<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;

final class LocalImageServerFactory implements ImageServerFactoryInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface  */
	private $responseFactory;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface $responseFactory
	 */
	public function __construct(ResponseFactoryInterface $responseFactory)
	{
		$this->responseFactory = $responseFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(ImageStorageInterface $imageStorage): ImageServerInterface
	{
		return new LocalImageServer($imageStorage, $this->responseFactory);
	}
}
