<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use function file_get_contents;

class ImageResource implements ResourceInterface
{
    private bool $modified = false;

    private ?string $encodeFormat = null;

    private ?string $encodedImage = null;

    public function __construct(
        private PathInfoInterface $pathInfo,
        private Image $image,
        private readonly string $localFilename,
        private readonly ModifierFacadeInterface $modifierFacade,
        private int $encodeQuality,
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

    public function modifyImage(string|array $modifiers, bool $stripMeta = false): self
    {
        $resource = clone $this;
        $modifyResult = $this->modifierFacade->modifyImage($this->image, $this->pathInfo, $modifiers, $stripMeta);
        $resource->image = $modifyResult->image;

        if ($modifyResult->modified) {
            $resource->modified = $modifyResult->modified;
            $resource->encodedImage = null;
        }

        if (null !== $modifyResult->encodeFormat) {
            $resource->encodeFormat = $modifyResult->encodeFormat;
        }

        if (null !== $modifyResult->encodeQuality) {
            $resource->encodeQuality = $modifyResult->encodeQuality;
        }

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

    public function getEncodeQuality(): int
    {
        return $this->encodeQuality;
    }

    public function getEncodeFormat(): ?string
    {
        return $this->encodeFormat;
    }

    public function getEncodedImage(): string
    {
        if (null !== $this->encodedImage) {
            return $this->encodedImage;
        }

        if (!$this->hasBeenModified()) {
            $contents = @file_get_contents($this->getLocalFilename());

            if (false !== $contents) {
                return $this->encodedImage = $contents;
            }
        }

        $format = $this->getEncodeFormat() ?? '';
        $image = $this->getSource();
        $image = $image->encode($format, $this->getEncodeQuality());

        return $this->encodedImage = $image->getEncoded();
    }
}
