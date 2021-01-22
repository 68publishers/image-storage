<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;

/**
 * @method ImagePathInfoInterface createPathInfo(string $path)
 * @method ImageFileInfoInterface createFileInfo(ImagePathInfoInterface $pathInfo)
 */
interface ImageStorageInterface extends FileStorageInterface, ImageServerInterface, NoImageResolverInterface, LinkGeneratorInterface
{
}
