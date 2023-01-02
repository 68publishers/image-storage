<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;

interface ImageStorageInterface extends FileStorageInterface, ImageServerInterface, NoImageResolverInterface, LinkGeneratorInterface
{
	public function createPathInfo(string $path/*, string|array|null $modifier = null*/): ImagePathInfoInterface;

	public function createFileInfo(PathInfoInterface $pathInfo): ImageFileInfoInterface;
}
