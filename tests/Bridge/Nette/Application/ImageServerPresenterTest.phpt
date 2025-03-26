<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\Application;

use Mockery;
use Nette\Application\Request as ApplicationRequest;
use Nette\Http\Request as NetteRequest;
use Nette\Http\UrlScript;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\Application\ImageServerPresenter;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ErrorResponse;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ImageResponse;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Request as ImageServerRequest;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use Stringable;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class ImageServerPresenterTest extends TestCase
{
    public function testExceptionShouldBeThrownIfStorageIsNotInstanceOfImageStorageInterface(): void
    {
        $netteRequest = new NetteRequest(
            new UrlScript('https://www.example.com/images/test/w:100/image.png?_v=123'),
        );
        $fileStorageProvider = Mockery::mock(FileStorageProviderInterface::class);
        $fileStorage = Mockery::mock(FileStorageInterface::class);

        $fileStorage->shouldReceive('getName')
            ->once()
            ->withNoArgs()
            ->andReturn('default');

        $fileStorageProvider->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($fileStorage);

        $applicationRequest = new ApplicationRequest('ImageStorage:ImageServer:default', 'GET', [
            '__storageName' => 'default',
        ]);

        $presenter = new ImageServerPresenter($netteRequest, $fileStorageProvider);

        Assert::exception(
            static fn () => $presenter->run($applicationRequest),
            InvalidStateException::class,
            'File storage "default" must be implementor of an interface SixtyEightPublishers\ImageStorage\ImageStorageInterface.',
        );
    }

    public function testImageResponseShouldBeReturned(): void
    {
        $netteRequest = new NetteRequest(
            new UrlScript('https://www.example.com/images/test/w:100/image.png?_v=123'),
        );
        $fileStorageProvider = Mockery::mock(FileStorageProviderInterface::class);
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $imageResponse = Mockery::mock(ImageResponse::class);

        $fileStorageProvider->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($imageStorage);

        $imageStorage->shouldReceive('getImageResponse')
            ->once()
            ->with(Mockery::type(ImageServerRequest::class))
            ->andReturnUsing(static function (ImageServerRequest $request) use ($netteRequest, $imageResponse): ImageResponse {
                Assert::same($netteRequest, $request->getOriginalRequest());

                return $imageResponse;
            });

        $applicationRequest = new ApplicationRequest('ImageStorage:ImageServer:default', 'GET', [
            '__storageName' => 'default',
        ]);

        $presenter = new ImageServerPresenter($netteRequest, $fileStorageProvider);

        Assert::same($imageResponse, $presenter->run($applicationRequest));
    }

    public function testErrorResponseShouldBeReturned(): void
    {
        $netteRequest = new NetteRequest(
            new UrlScript('https://www.example.com/images/test/w:100/image.png?_v=123'),
        );
        $fileStorageProvider = Mockery::mock(FileStorageProviderInterface::class);
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $errorResponse = Mockery::mock(ErrorResponse::class);

        $fileStorageProvider->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($imageStorage);

        $imageStorage->shouldReceive('getImageResponse')
            ->once()
            ->with(Mockery::type(ImageServerRequest::class))
            ->andReturnUsing(static function (ImageServerRequest $request) use ($netteRequest, $errorResponse): ErrorResponse {
                Assert::same($netteRequest, $request->getOriginalRequest());

                return $errorResponse;
            });

        $applicationRequest = new ApplicationRequest('ImageStorage:ImageServer:default', 'GET', [
            '__storageName' => 'default',
        ]);

        $presenter = new ImageServerPresenter($netteRequest, $fileStorageProvider);

        Assert::same($errorResponse, $presenter->run($applicationRequest));
    }

    /**
     * @dataProvider testErrorResponseShouldBeReturnedAndLoggedOnServerErrorDataProvider
     */
    public function testErrorResponseShouldBeReturnedAndLoggedOnServerError(ResponseException $exception, array $logLines): void
    {
        $netteRequest = new NetteRequest(
            new UrlScript('https://www.example.com/images/test/w:100/image.png?_v=123'),
        );
        $fileStorageProvider = Mockery::mock(FileStorageProviderInterface::class);
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $errorResponse = Mockery::mock(ErrorResponse::class);
        $logger = new class implements LoggerInterface {
            use LoggerTrait;

            public array $records = [];

            public function log($level, string|Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => $level,
                    'message' => $message,
                    'context' => $context,
                ];
            }
        };

        $fileStorageProvider->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($imageStorage);

        $imageStorage->shouldReceive('getImageResponse')
            ->once()
            ->with(Mockery::type(ImageServerRequest::class))
            ->andReturnUsing(static function (ImageServerRequest $request) use ($netteRequest, $errorResponse): ErrorResponse {
                Assert::same($netteRequest, $request->getOriginalRequest());

                return $errorResponse;
            });

        $errorResponse->shouldReceive('getException')
            ->once()
            ->withNoArgs()
            ->andReturn($exception);

        $applicationRequest = new ApplicationRequest('ImageStorage:ImageServer:default', 'GET', [
            '__storageName' => 'default',
        ]);

        $presenter = new ImageServerPresenter($netteRequest, $fileStorageProvider, $logger);

        Assert::same($errorResponse, $presenter->run($applicationRequest));
        Assert::same($logLines, $logger->records);
    }

    public function testErrorResponseShouldBeReturnedAndLoggedOnServerErrorDataProvider(): array
    {
        return [
            'Non server error should be logged' => [
                new ResponseException('File not found.', 404),
                [],
            ],
            'Server error should be logged' => [
                $exception = new ResponseException('Internal server error.', 500),
                [
                    [
                        'level' => LogLevel::ERROR,
                        'message' => 'Internal server error.',
                        'context' => [
                            'exception' => $exception,
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ImageServerPresenterTest())->run();
