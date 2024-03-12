<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\ImageServer;

use Closure;
use League\Flysystem\FilesystemReader;
use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ImageResponse;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ResponseFactory;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.php';

final class ResponseFactoryTest extends TestCase
{
    public function testImageResponseShouldBeCreated(): void
    {
        $filesystem = Mockery::mock(FilesystemReader::class);
        $config = Mockery::mock(ConfigInterface::class);
        $factory = new ResponseFactory();

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::CACHE_MAX_AGE)
            ->andReturn(31536000);

        $response = $factory->createImageResponse($filesystem, 'test/w:100/image.png', $config);

        call_user_func(Closure::bind(
            static function () use ($response, $filesystem): void {
                Assert::same($filesystem, $response->filesystemReader);
                Assert::same('test/w:100/image.png', $response->filePath);
                Assert::same(31536000, $response->maxAge);
            },
            null,
            ImageResponse::class,
        ));
    }

    public function testErrorResponseShouldBeCreated(): void
    {
        $exception = new ResponseException('File not found.', 404);
        $config = Mockery::mock(ConfigInterface::class);
        $factory = new ResponseFactory();
        $response = $factory->createErrorResponse($exception, $config);

        Assert::same($exception, $response->getException());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ResponseFactoryTest())->run();
