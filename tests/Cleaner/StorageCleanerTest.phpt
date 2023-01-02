<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Cleaner;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use League\Flysystem\FilesystemOperator;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem as ImageFilesystem;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager as ImageMountManager;

require __DIR__ . '/../bootstrap.php';

final class StorageCleanerTest extends TestCase
{
	public function testCountShouldBeReturnedWithOriginalFilesystemInstance(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(FilesystemOperator::class);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($filesystem, [])
			->andReturn(10);

		$cleaner = new StorageCleaner($innerCleaner);

		Assert::same(10, $cleaner->getCount($filesystem, []));
	}

	public function testCountShouldBeReturnedWithImageFilesystem(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(ImageFilesystem::class);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($filesystem, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturn(10);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($filesystem, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE,
			])
			->andReturn(3);

		$cleaner = new StorageCleaner($innerCleaner);

		Assert::same(13, $cleaner->getCount($filesystem, []));
	}

	public function testCountShouldBeReturnedWithImageMountManager(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$mountManager = Mockery::mock(ImageMountManager::class);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($mountManager, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturn(10);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($mountManager, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE,
			])
			->andReturn(3);

		$cleaner = new StorageCleaner($innerCleaner);

		Assert::same(13, $cleaner->getCount($mountManager, []));
	}

	public function testCountShouldBeReturnedWithImageFilesystemAndCacheOnlyOption(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(ImageFilesystem::class);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($filesystem, [
				StorageCleaner::OPTION_CACHE_ONLY => true,
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturn(10);

		$cleaner = new StorageCleaner($innerCleaner);

		Assert::same(10, $cleaner->getCount($filesystem, [
			StorageCleaner::OPTION_CACHE_ONLY => true,
		]));
	}

	public function testCountShouldBeReturnedWithImageMountManagerAndCacheOnlyOption(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$mountManager = Mockery::mock(ImageMountManager::class);

		$innerCleaner->shouldReceive('getCount')
			->once()
			->with($mountManager, [
				StorageCleaner::OPTION_CACHE_ONLY => true,
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturn(10);

		$cleaner = new StorageCleaner($innerCleaner);

		Assert::same(10, $cleaner->getCount($mountManager, [
			StorageCleaner::OPTION_CACHE_ONLY => true,
		]));
	}

	public function testStorageShouldBeCleanedWithOriginalFilesystemInstance(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(FilesystemOperator::class);

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($filesystem, [])
			->andReturns();

		$cleaner = new StorageCleaner($innerCleaner);

		$cleaner->clean($filesystem, []);
	}

	public function testStorageShouldBeCleanedWithImageFilesystem(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(ImageFilesystem::class);

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($filesystem, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturns();

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($filesystem, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE,
			])
			->andReturns();

		$cleaner = new StorageCleaner($innerCleaner);

		$cleaner->clean($filesystem, []);
	}

	public function testStorageShouldBeCleanedWithImageMountManager(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$mountManager = Mockery::mock(ImageMountManager::class);

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($mountManager, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturns();

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($mountManager, [
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_SOURCE,
			])
			->andReturns();

		$cleaner = new StorageCleaner($innerCleaner);

		$cleaner->clean($mountManager, []);
	}

	public function testStorageShouldBeCleanedWithImageFilesystemAndCacheOnlyOption(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$filesystem = Mockery::mock(ImageFilesystem::class);

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($filesystem, [
				StorageCleaner::OPTION_CACHE_ONLY => true,
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturns();

		$cleaner = new StorageCleaner($innerCleaner);

		$cleaner->clean($filesystem, [
			StorageCleaner::OPTION_CACHE_ONLY => true,
		]);
	}

	public function testStorageShouldBeCleanedWithImageMountManagerAndCacheOnlyOption(): void
	{
		$innerCleaner = Mockery::mock(StorageCleanerInterface::class);
		$mountManager = Mockery::mock(ImageMountManager::class);

		$innerCleaner->shouldReceive('clean')
			->once()
			->with($mountManager, [
				StorageCleaner::OPTION_CACHE_ONLY => true,
				StorageCleanerInterface::OPTION_FILESYSTEM_PREFIX => ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE,
			])
			->andReturns();

		$cleaner = new StorageCleaner($innerCleaner);

		$cleaner->clean($mountManager, [
			StorageCleaner::OPTION_CACHE_ONLY => true,
		]);
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new StorageCleanerTest())->run();
