<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\FileInfo as BaseFileInfo;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface as ImageLinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use function assert;

final class FileInfo extends BaseFileInfo implements FileInfoInterface
{
    public function __construct(ImageLinkGeneratorInterface $linkGenerator, PathInfoInterface $pathInfo, string $imageStorageName)
    {
        parent::__construct($linkGenerator, $pathInfo, $imageStorageName);
    }

    public function srcSet(DescriptorInterface $descriptor): string
    {
        assert($this->linkGenerator instanceof ImageLinkGeneratorInterface);

        return $this->linkGenerator->srcSet($this, $descriptor);
    }

    public function getModifiers(): string|array|null
    {
        return $this->pathInfo instanceof ImagePathInfoInterface ? $this->pathInfo->getModifiers() : null;
    }

    public function withModifiers(string|array|null $modifiers): static
    {
        $pathInfo = $this->checkPathInfoType(__METHOD__);
        $info = clone $this;
        $info->pathInfo = $pathInfo->withModifiers($modifiers);

        return $info;
    }

    public function withEncodedModifiers(string $modifiers): static
    {
        $pathInfo = $this->checkPathInfoType(__METHOD__);
        $info = clone $this;
        $info->pathInfo = $pathInfo->withEncodedModifiers($modifiers);

        return $info;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        # A path doesn't contain an extension if a modifier is NULL
        if ($this->pathInfo instanceof ImagePathInfoInterface && null === $this->getModifiers() && null !== $this->getExtension()) {
            # Save with the default file extension
            $array['path'] .= '.' . $this->getExtension();
        }

        return $array;
    }

    /**
     * @throws InvalidStateException
     */
    private function checkPathInfoType(string $method): ImagePathInfoInterface
    {
        $pathInfo = $this->pathInfo;

        if (!$pathInfo instanceof ImagePathInfoInterface) {
            throw new InvalidStateException(sprintf(
                'An instance of %s must be implementor of an interface %s if you want to use the method %s().',
                get_class($this->pathInfo),
                ImagePathInfoInterface::class,
                $method,
            ));
        }

        return $pathInfo;
    }
}
