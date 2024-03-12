<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use function sprintf;

final class ExternalImageServerFactory implements ImageServerFactoryInterface
{
    /**
     * @throws InvalidStateException
     */
    public function create(ImageStorageInterface $imageStorage): ImageServerInterface
    {
        throw new InvalidStateException(sprintf(
            'ImageServer for the image storage "%s" is external.',
            $imageStorage->getName(),
        ));
    }
}
