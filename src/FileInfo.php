<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\FileInfo as BaseFileInfo;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;

final class FileInfo extends BaseFileInfo implements FileInfoInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface $linkGenerator
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface                     $pathInfo
	 * @param string                                                                  $imageStorageName
	 */
	public function __construct(LinkGeneratorInterface $linkGenerator, PathInfoInterface $pathInfo, string $imageStorageName)
	{
		parent::__construct($linkGenerator, $pathInfo, $imageStorageName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function srcSet(DescriptorInterface $descriptor): string
	{
		return $this->linkGenerator->srcSet($this, $descriptor);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModifiers()
	{
		return $this->pathInfo instanceof PathInfoInterface ? $this->pathInfo->getModifiers() : NULL;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withModifiers($modifiers): self
	{
		$this->checkPathInfoType();

		return new static($this->linkGenerator, $this->pathInfo->withModifiers($modifiers), $this->getStorageName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function withEncodedModifiers(string $modifiers)
	{
		$this->checkPathInfoType();

		return new static($this->linkGenerator, $this->pathInfo->withEncodedModifiers($modifiers), $this->getStorageName());
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize(): array
	{
		$json = parent::jsonSerialize();

		# A path doesn't contain an extension if a modifier is NULL
		if (NULL === $this->getModifiers() && NULL !== $this->getExtension()) {
			# Save with the default file extension
			$json['path'] .= '.' . $this->getExtension();
		}

		return $json;
	}

	/**
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	private function checkPathInfoType(): void
	{
		if (!$this->pathInfo instanceof PathInfoInterface) {
			throw new InvalidStateException(sprintf(
				'An instance of %s must be implementor of an interface %s if you want to use method %s.',
				get_class($this->pathInfo),
				PathInfoInterface::class,
				__METHOD__
			));
		}
	}
}
