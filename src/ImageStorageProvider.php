<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use Nette;
use SixtyEightPublishers;

final class ImageStorageProvider implements IImageStorageProvider
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorage */
	private $defaultImageStorage;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorage[]  */
	private $imageStorages = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorage $defaultImageStorage
	 * @param array                                            $imageStorages
	 */
	public function __construct(IImageStorage $defaultImageStorage, array $imageStorages)
	{
		$this->defaultImageStorage = $defaultImageStorage;
		$this->addImageStorage($defaultImageStorage);

		foreach ($imageStorages as $imageStorage) {
			$this->addImageStorage($imageStorage);
		}
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage
	 *
	 * @return void
	 */
	private function addImageStorage(IImageStorage $imageStorage): void
	{
		$this->imageStorages[$imageStorage->getName()] = $imageStorage;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\IImageStorage
	 */
	private function getDefaultImageStorage(): IImageStorage
	{
		if (NULL === $this->defaultImageStorage) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException('Default ImageStorage is not defined.');
		}

		return $this->defaultImageStorage;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\IImageStorageProvider **************/

	/**
	 * {@inheritdoc}
	 */
	public function get(?string $name = NULL): IImageStorage
	{
		if (NULL === $name) {
			return $this->getDefaultImageStorage();
		}

		if (!isset($this->imageStorages[$name])) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'ImageStorage with name "%s" is not defined.',
				$name
			));
		}

		return $this->imageStorages[$name];
	}
}
