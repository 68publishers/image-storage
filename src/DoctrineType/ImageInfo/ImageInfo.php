<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo;

use SixtyEightPublishers;

class ImageInfo extends SixtyEightPublishers\ImageStorage\ImageInfo implements \JsonSerializable
{
	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator  */
	private $linkGenerator;

	/** @var string  */
	private $imageStorageName;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                    $info
	 * @param string                                                          $imageStorageName
	 * @param string|NULL                                                     $version
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator,
		SixtyEightPublishers\ImageStorage\ImageInfo $info,
		string $imageStorageName,
		?string $version = NULL
	) {
		parent::__construct((string) $info);

		$this->linkGenerator = $linkGenerator;
		$this->imageStorageName = $imageStorageName;
		$this->setVersion($version);
	}

	/**
	 * @return string
	 */
	public function getStorageName(): string
	{
		return $this->imageStorageName;
	}

	/**
	 * @param array|string $modifier
	 *
	 * @return string
	 */
	public function link($modifier): string
	{
		return $this->linkGenerator->link($this, $modifier);
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param array|string|NULL                                                    $modifier
	 *
	 * @return string
	 */
	public function srcSet(SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor, $modifier = NULL): string
	{
		return $this->linkGenerator->srcSet($this, $descriptor, $modifier);
	}

	/************** interface \JsonSerializable **************/

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize(): array
	{
		return [
			'path' => (string) $this,
			'generator' => $this->getStorageName(),
			'version' => $this->getVersion(),
		];
	}
}
