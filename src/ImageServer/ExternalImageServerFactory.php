<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;

final class ExternalImageServerFactory implements ImageServerFactoryInterface
{
	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function create(ImageStorageInterface $imageStorage): ImageServerInterface
	{
		throw new InvalidStateException(sprintf(
			'ImageServer for an image storage "%s" is external.',
			$imageStorage->getName()
		));
	}
}
