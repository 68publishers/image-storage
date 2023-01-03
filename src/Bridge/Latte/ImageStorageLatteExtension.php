<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Latte;

use Latte\Extension;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;

final class ImageStorageLatteExtension extends Extension
{
	public function __construct(
		private readonly FileStorageProviderInterface $fileStorageProvider,
	) {
	}

	public function getFunctions(): array
	{
		return [
			'w_descriptor' => static fn (int ...$widths): WDescriptor => new WDescriptor(...$widths),
			'w_descriptor_range' => static fn (int $min, int $max, int $step = 100): WDescriptor => WDescriptor::fromRange($min, $max, $step),
			'x_descriptor' => static fn (...$pixelDensities): XDescriptor => empty($pixelDensities) ? XDescriptor::default() : new XDescriptor(...$pixelDensities),
			'no_image' => fn (?string $noImageName = null, ?string $storageName = null) => $this->createNoImage($noImageName, $storageName),
		];
	}

	private function createNoImage(?string $noImageName = null, ?string $storageName = null): ImageFileInfoInterface
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
