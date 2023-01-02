<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use function sprintf;

final class ExternalImageServerFactory implements ImageServerFactoryInterface
{
	/**
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function create(ImageStorageInterface $imageStorage): ImageServerInterface
	{
		throw new InvalidStateException(sprintf(
			'ImageServer for the image storage "%s" is external.',
			$imageStorage->getName()
		));
	}
}
