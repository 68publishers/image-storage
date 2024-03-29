<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use function in_array;
use function preg_match;
use function sprintf;

final class NoImageResolver implements NoImageResolverInterface
{
    public function __construct(
        private readonly InfoFactoryInterface $infoFactory,
        private readonly NoImageConfigInterface $noImageConfig,
    ) {}

    public function getNoImageConfig(): NoImageConfigInterface
    {
        return $this->noImageConfig;
    }

    public function getNoImage(?string $name = null): PathInfoInterface
    {
        if (null === $name) {
            return $this->getDefaultPathInfo();
        }

        $paths = $this->noImageConfig->getPaths();

        if (!isset($paths[$name])) {
            throw new InvalidArgumentException(sprintf(
                'No-image with name "%s" is not defined.',
                $name,
            ));
        }

        return $this->infoFactory->createPathInfo($paths[$name]);
    }

    public function isNoImage(string $path): bool
    {
        return $path === $this->noImageConfig->getDefaultPath() || in_array($path, $this->noImageConfig->getPaths(), true);
    }

    public function resolveNoImage(string $path): PathInfoInterface
    {
        foreach ($this->noImageConfig->getPatterns() as $name => $regex) {
            if (preg_match('#' . $regex . '#', $path)) {
                return $this->getNoImage($name);
            }
        }

        return $this->getNoImage();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getDefaultPathInfo(): PathInfoInterface
    {
        if (null === $this->noImageConfig->getDefaultPath()) {
            throw new InvalidArgumentException('Default no-image path is not defined.');
        }

        return $this->infoFactory->createPathInfo($this->noImageConfig->getDefaultPath());
    }
}
