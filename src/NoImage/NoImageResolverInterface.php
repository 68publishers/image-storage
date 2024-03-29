<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;

interface NoImageResolverInterface
{
    public function getNoImageConfig(): NoImageConfigInterface;

    /**
     * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
     */
    public function getNoImage(?string $name = null): PathInfoInterface;

    public function isNoImage(string $path): bool;

    /**
     * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
     */
    public function resolveNoImage(string $path): PathInfoInterface;
}
