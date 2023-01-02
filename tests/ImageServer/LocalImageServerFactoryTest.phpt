<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\ImageServer;

use Closure;
use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServer;
use SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServerFactory;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;
use function assert;
use function call_user_func;

require __DIR__ . '/../bootstrap.php';

final class LocalImageServerFactoryTest extends TestCase
{
	public function testServerShouldBeCreated(): void
	{
		$responseFactory = Mockery::mock(ResponseFactoryInterface::class);
		$imageStorage = Mockery::mock(ImageStorageInterface::class);
		$factory = new LocalImageServerFactory($responseFactory);

		$server = $factory->create($imageStorage);

		Assert::type(LocalImageServer::class, $server);
		assert($server instanceof LocalImageServer);

		call_user_func(Closure::bind(
			static function () use ($server, $responseFactory, $imageStorage): void {
				Assert::same($responseFactory, $server->responseFactory);
				Assert::same($imageStorage, $server->imageStorage);
			},
			null,
			LocalImageServer::class
		));
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new LocalImageServerFactoryTest())->run();
