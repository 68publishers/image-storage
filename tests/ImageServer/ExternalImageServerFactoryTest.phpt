<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\ImageServer;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\ImageServer\ExternalImageServerFactory;

require __DIR__ . '/../bootstrap.php';

final class ExternalImageServerFactoryTest extends TestCase
{
	public function testExceptionShouldBeThrownOnServerCreation(): void
	{
		$factory = new ExternalImageServerFactory();
		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('default');

		Assert::exception(
			static fn () => $factory->create($imageStorage),
			InvalidStateException::class,
			'ImageServer for the image storage "default" is external.'
		);
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ExternalImageServerFactoryTest())->run();
