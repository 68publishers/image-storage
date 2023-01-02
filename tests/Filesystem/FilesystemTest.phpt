<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Filesystem;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use League\Flysystem\FilesystemAdapter;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

require __DIR__ . '/../bootstrap.php';

final class FilesystemTest extends TestCase
{
	public function testExceptionShouldBeThrownIfNameArgumentPassed(): void
	{
		$adapter = Mockery::mock(FilesystemAdapter::class);
		$filesystem = new Filesystem($adapter);

		Assert::exception(
			static fn () => $filesystem->getAdapter('test'),
			InvalidArgumentException::class,
			'The filesystem is non-prefixed.'
		);
	}

	public function testAdapterShouldBeReturned(): void
	{
		$adapter = Mockery::mock(FilesystemAdapter::class);
		$filesystem = new Filesystem($adapter);

		Assert::same($adapter, $filesystem->getAdapter());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new FilesystemTest())->run();
