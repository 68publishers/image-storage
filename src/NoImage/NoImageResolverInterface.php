<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;

interface NoImageResolverInterface
{
    public function getNoImageConfig(): NoImageConfigInterface;

    /**
     * @throws InvalidArgumentException
     */
    public function getNoImage(?string $name = null): PathInfoInterface;

    public function isNoImage(string $path): bool;

    /**
     * @throws InvalidArgumentException
     */
    public function resolveNoImage(string $path): PathInfoInterface;
}
