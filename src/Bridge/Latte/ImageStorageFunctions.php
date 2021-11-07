<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Latte;

use Latte\Engine;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;

final class ImageStorageFunctions
{
	public const FUNCTION_ID_CREATE_W_DESCRIPTOR = 'create_w_descriptor';
	public const FUNCTION_ID_CREATE_X_DESCRIPTOR = 'create_x_descriptor';
	public const FUNCTION_ID_CREATE_W_DESCRIPTOR_FROM_RANGE = 'create_w_descriptor_from_range';
	public const FUNCTION_ID_CREATE_NO_IMAGE = 'create_no_image';

	public const DEFAULT_FUNCTION_NAMES = [
		self::FUNCTION_ID_CREATE_W_DESCRIPTOR => 'w_descriptor',
		self::FUNCTION_ID_CREATE_X_DESCRIPTOR => 'x_descriptor',
		self::FUNCTION_ID_CREATE_W_DESCRIPTOR_FROM_RANGE => 'w_descriptor_range',
		self::FUNCTION_ID_CREATE_NO_IMAGE => 'no_image',
	];

	private const FUNCTION_CALLBACKS = [
		self::FUNCTION_ID_CREATE_W_DESCRIPTOR => 'createWDescriptor',
		self::FUNCTION_ID_CREATE_X_DESCRIPTOR => 'createXDescriptor',
		self::FUNCTION_ID_CREATE_W_DESCRIPTOR_FROM_RANGE => 'createWDescriptorFromRange',
		self::FUNCTION_ID_CREATE_NO_IMAGE => 'createNoImage',
	];

	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 */
	public function __construct(FileStorageProviderInterface $fileStorageProvider)
	{
		$this->fileStorageProvider = $fileStorageProvider;
	}

	/**
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 * @param \Latte\Engine                                                  $engine
	 * @param array                                                          $customFunctionNames
	 *
	 * @return void
	 */
	public static function register(FileStorageProviderInterface $fileStorageProvider, Engine $engine, array $customFunctionNames = []): void
	{
		$me = new static($fileStorageProvider);

		foreach (array_merge(self::DEFAULT_FUNCTION_NAMES, $customFunctionNames) as $functionId => $functionName) {
			$engine->addFunction($functionName, [$me, self::FUNCTION_CALLBACKS[$functionId]]);
		}
	}

	/**
	 * @param int ...$widths
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface
	 */
	public function createWDescriptor(int ...$widths): DescriptorInterface
	{
		return new WDescriptor(...$widths);
	}

	/**
	 * @param int $min
	 * @param int $max
	 * @param int $step
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface
	 */
	public function createWDescriptorFromRange(int $min, int $max, int $step = 100): DescriptorInterface
	{
		return WDescriptor::fromRange($min, $max, $step);
	}

	/**
	 * @param mixed ...$pixelDensities
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface
	 */
	public function createXDescriptor(...$pixelDensities): DescriptorInterface
	{
		if (empty($pixelDensities)) {
			return XDescriptor::default();
		}

		return new XDescriptor(...$pixelDensities);
	}

	/**
	 * @param string|NULL $noImageName
	 * @param string|NULL $storageName
	 *
	 * @return \SixtyEightPublishers\ImageStorage\FileInfoInterface
	 */
	public function createNoImage(?string $noImageName = NULL, ?string $storageName = NULL): ImageFileInfoInterface
	{
		$storage = $this->fileStorageProvider->get($storageName);

		if (!$storage instanceof ImageStorageInterface) {
			throw new InvalidArgumentException(sprintf(
				'Storage "%s" must be implementor of %s.',
				$storage->getName(),
				ImageStorageInterface::class
			));
		}

		return $storage->createFileInfo($storage->getNoImage($noImageName));
	}
}
