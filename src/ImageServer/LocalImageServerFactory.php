<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageStorageInterface;

final class LocalImageServerFactory implements ImageServerFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function create(ImageStorageInterface $imageStorage): ImageServerInterface
    {
        return new LocalImageServer($imageStorage, $this->responseFactory);
    }
}
