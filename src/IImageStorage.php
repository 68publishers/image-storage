<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

interface IImageStorage extends LinkGenerator\ILinkGenerator, NoImage\INoImageResolver, Resource\IResourceFactory, ImagePersister\IImagePersister, ImageServer\IImageServer
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Config\Config
	 */
	public function getConfig(): Config\Config;

	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function createImageInfo(string $path): ImageInfo;
}
