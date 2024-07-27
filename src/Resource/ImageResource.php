<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

class ImageResource implements ResourceInterface
{
    private bool $modified = false;

    public function __construct(
        private PathInfoInterface $pathInfo,
        private Image $image,
        private readonly string $localFilename,
        private readonly ModifierFacadeInterface $modifierFacade,
    ) {}

    public function getPathInfo(): PathInfoInterface
    {
        return $this->pathInfo;
    }

    public function getSource(): Image
    {
        return $this->image;
    }

    public function getLocalFilename(): string
    {
        return $this->localFilename;
    }

    public function hasBeenModified(): bool
    {
        return $this->modified;
    }

    public function withPathInfo(PathInfoInterface $pathInfo): self
    {
        $resource = clone $this;
        $resource->pathInfo = $pathInfo;

        return $resource;
    }

    public function modifyImage(string|array $modifiers): self
    {
        $resource = clone $this;
        $modifyResult = $this->modifierFacade->modifyImage($this->image, $this->pathInfo, $modifiers);
        $resource->image = $modifyResult->image;
        $resource->modified = $modifyResult->modified;

        return $resource;
    }

    public function getMimeType(): ?string
    {
        return $this->image->mime();
    }

    public function getFilesize(): ?int
    {
        $filesize = null !== $this->image->basePath() ? $this->image->filesize() : false; # @phpstan-ignore-line ternary.alwaysTrue

        return false !== $filesize ? (int) $filesize : null;
    }
}
