<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Assets;

use Nette;
use SixtyEightPublishers;

final class StorageAssets implements \IteratorAggregate, \Countable
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorage  */
	private $imageStorage;

	/** @var \SixtyEightPublishers\ImageStorage\Assets\Asset[]  */
	private $assets = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage)
	{
		$this->imageStorage = $imageStorage;
	}

	/**
	 * @param string $from
	 * @param string $to
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\AssetException
	 */
	public function add(string $from, string $to): void
	{
		if (is_file($from)) {
			$this->assets[] = Asset::fromFile($from, $to);
		} elseif (is_dir($from)) {
			$this->assets = array_merge($this->assets, Asset::fromDirectory($from, $to));
		} else {
			throw SixtyEightPublishers\ImageStorage\Exception\AssetException::invalidAsset($from);
		}
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->imageStorage->getName();
	}

	/**
	 * @return void
	 */
	public function persist(): void
	{
		foreach ($this->assets as $asset) {
			$resource = $this->imageStorage->createResourceFromLocalFile(
				$this->imageStorage->createImageInfo($asset->getOutputPath()),
				$asset->getFileInfo()->getRealPath()
			);

			try {
				$this->imageStorage->update($resource);
			} catch (SixtyEightPublishers\ImageStorage\Exception\IException $e) {
				$this->imageStorage->save($resource);
			}
		}
	}

	/********************** interface \IteratorAggregate **********************/

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->assets);
	}

	/********************** interface \Countable **********************/

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->assets);
	}
}
