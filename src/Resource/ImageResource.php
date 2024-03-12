<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

class ImageResource implements ResourceInterface
{
	public function __construct(
		private PathInfoInterface $pathInfo,
		private Image $image,
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

	public function withPathInfo(PathInfoInterface $pathInfo): static
	{
		$resource = clone $this;
		$resource->pathInfo = $pathInfo;

		return $resource;
	}

	public function modifyImage(string|array $modifiers): static
	{
		$resource = clone $this;
		$resource->image = $this->modifierFacade->modifyImage($this->image, $this->pathInfo, $modifiers);

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
