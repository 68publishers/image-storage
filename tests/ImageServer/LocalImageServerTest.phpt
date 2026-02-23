<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\ImageServer;

use League\Flysystem\FilesystemOperator;
use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServer;
use SixtyEightPublishers\ImageStorage\ImageServer\RequestInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ResponseFactoryInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Resource\ResourceInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class LocalImageServerTest extends TestCase
{
    public function testErrorResponseShouldBeReturnedIfSignatureParameterIsMissing(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);
        $config = $this->createConfig('', true);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectErrorResponse($response, 'Request contains invalid signature.', 403));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn($signatureStrategy);

        $signatureStrategy->shouldReceive('verifyToken')
            ->once()
            ->with('', 'path/w:100/image.png')
            ->andReturn(false);

        Assert::same($response, $server->getImageResponse($this->createRequest('/path/w:100/image.png', null)));
    }

    public function testErrorResponseShouldBeReturnedIfSignatureIsNotVerified(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);
        $config = $this->createConfig('', true);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectErrorResponse($response, 'Request contains invalid signature.', 403));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn($signatureStrategy);

        $signatureStrategy->shouldReceive('verifyToken')
            ->once()
            ->with('__token__', 'path/w:100/image.png')
            ->andReturn(false);

        Assert::same($response, $server->getImageResponse($this->createRequest('/path/w:100/image.png', '__token__')));
    }

    public function testErrorResponseShouldBeReturnedIfUrlPathDoesNotContainModifiers(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectErrorResponse($response, 'Internal server error. Missing modifier in requested path.', 500));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        Assert::same($response, $server->getImageResponse($this->createRequest('/image.png', false)));
    }

    public function testErrorResponseShouldBeReturnedIfUrlPathDoesNotContainExtension(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectErrorResponse($response, 'Internal server error. Missing file extension in requested path.', 500));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image', false)));
    }

    public function testErrorResponseShouldBeReturnedIfFileNotFoundAndNoImageCanNotBeResolved(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectErrorResponse($response, 'Source file not found.', 404));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(false);

        $imageStorage->shouldReceive('createResource')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andThrows(new FileNotFoundException('path/image.png'));

        $pathInfoWithEncodedModifiers->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 100]);

        $pathInfoForNoImageResolving = Mockery::mock(PathInfoInterface::class);

        $pathInfoWithEncodedModifiers->shouldReceive('withModifiers')
            ->once()
            ->with(null)
            ->andReturn($pathInfoForNoImageResolving);

        $pathInfoForNoImageResolving->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/image.png');

        $imageStorage->shouldReceive('resolveNoImage')
            ->once()
            ->with('path/image.png')
            ->andThrows(new InvalidArgumentException('Can not resolve no image.'));

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image.png', false)));
    }

    public function testImageResponseShouldBeReturnedIfCachedFileExists(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectImageResponse($response, 'cache://path/w:100/image.png'));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(true);

        $pathInfoWithEncodedModifiers->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/w:100/image.png');

        $imageStorage->shouldReceive('getFilesystem')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::mock(FilesystemOperator::class));

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image.png', false)));
    }

    public function testBasePathShouldBeStripped(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('images', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectImageResponse($response, 'cache://path/w:100/image.png'));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(true);

        $pathInfoWithEncodedModifiers->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/w:100/image.png');

        $imageStorage->shouldReceive('getFilesystem')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::mock(FilesystemOperator::class));

        Assert::same($response, $server->getImageResponse($this->createRequest('/images/path/w:100/image.png', false)));
    }

    public function testImageResponseShouldBeReturnedIfCachedFileExistsWithSignatureToken(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);
        $config = $this->createConfig('', true);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectImageResponse($response, 'cache://path/w:100/image.png'));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn($signatureStrategy);

        $signatureStrategy->shouldReceive('verifyToken')
            ->once()
            ->with('__token__', 'path/w:100/image.png')
            ->andReturn(true);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(true);

        $pathInfoWithEncodedModifiers->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/w:100/image.png');

        $imageStorage->shouldReceive('getFilesystem')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::mock(FilesystemOperator::class));

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image.png', '__token__')));
    }

    public function testImageResponseShouldBeReturnedIfOnlSourceFileExists(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectImageResponse($response, 'cache://path/w:100/image.png'));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(false);

        $resource = Mockery::mock(ResourceInterface::class);

        $imageStorage->shouldReceive('createResource')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn($resource);

        $imageStorage->shouldReceive('save')
            ->once()
            ->with($resource)
            ->andReturn('path/w:100/image.png');

        $imageStorage->shouldReceive('getFilesystem')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::mock(FilesystemOperator::class));

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image.png', false)));
    }

    public function testNoImageResponseShouldBeReturnedIfSourceFileNotExists(): void
    {
        $imageStorage = Mockery::mock(ImageStorageInterface::class);
        $config = $this->createConfig('', false);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $response = (object) ['test' => true];

        $server = new LocalImageServer($imageStorage, $this->expectImageResponse($response, 'cache://no-image/w:100/no-image.png'));

        $imageStorage->shouldReceive('getConfig')
            ->withNoArgs()
            ->andReturn($config);

        $imageStorage->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageStorage->shouldReceive('createPathInfo')
            ->once()
            ->with('path/image.png')
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $pathInfoWithEncodedModifiers = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('withEncodedModifiers')
            ->once()
            ->with('w:100')
            ->andReturn($pathInfoWithEncodedModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andReturn(false);

        $imageStorage->shouldReceive('createResource')
            ->once()
            ->with($pathInfoWithEncodedModifiers)
            ->andThrows(new FileNotFoundException('path/image.png'));

        $pathInfoWithEncodedModifiers->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 100]);

        $pathInfoForNoImageResolving = Mockery::mock(PathInfoInterface::class);

        $pathInfoWithEncodedModifiers->shouldReceive('withModifiers')
            ->once()
            ->with(null)
            ->andReturn($pathInfoForNoImageResolving);

        $pathInfoForNoImageResolving->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('path/image.png');

        $noImagePathInfo = Mockery::mock(PathInfoInterface::class);

        $imageStorage->shouldReceive('resolveNoImage')
            ->once()
            ->with('path/image.png')
            ->andReturn($noImagePathInfo);

        $pathInfoWithEncodedModifiers->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn('png');

        $noImagePathInfoWithExtension = Mockery::mock(PathInfoInterface::class);

        $noImagePathInfo->shouldReceive('withExtension')
            ->once()
            ->with('png')
            ->andReturn($noImagePathInfoWithExtension);

        $noImagePathInfoWithExtensionAndModifiers = Mockery::mock(PathInfoInterface::class);

        $noImagePathInfoWithExtension->shouldReceive('withModifiers')
            ->once()
            ->with(['w' => 100])
            ->andReturn($noImagePathInfoWithExtensionAndModifiers);

        $imageStorage->shouldReceive('exists')
            ->once()
            ->with($noImagePathInfoWithExtensionAndModifiers)
            ->andReturn(true);

        $noImagePathInfoWithExtensionAndModifiers->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('no-image/w:100/no-image.png');

        $imageStorage->shouldReceive('getFilesystem')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::mock(FilesystemOperator::class));

        Assert::same($response, $server->getImageResponse($this->createRequest('path/w:100/image.png', false)));
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    private function createConfig(string $basePath, bool $hasSignatureStrategy): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(ConfigInterface::BASE_PATH)
            ->andReturn($basePath);

        if ($hasSignatureStrategy) {
            $config->shouldReceive('offsetGet')
                ->once()
                ->with(Config::SIGNATURE_PARAMETER_NAME)
                ->andReturn('_s');
        }

        return $config;
    }

    private function createRequest(string $path, string|null|false $signature): RequestInterface
    {
        $request = Mockery::mock(RequestInterface::class);

        $request->shouldReceive('getUrlPath')
            ->once()
            ->withNoArgs()
            ->andReturn($path);

        if (false !== $signature) {
            $request->shouldReceive('getQueryParameter')
                ->once()
                ->with('_s')
                ->andReturn($signature);
        }

        return $request;
    }

    private function expectErrorResponse(object $response, string $message, int $code): ResponseFactoryInterface
    {
        $responseFactory = Mockery::mock(ResponseFactoryInterface::class);

        $responseFactory->shouldReceive('createErrorResponse')
            ->once()
            ->with(Mockery::type(ResponseException::class), Mockery::type(ConfigInterface::class))
            ->andReturnUsing(static function (ResponseException $exception) use ($response, $message, $code): object {
                Assert::same($message, $exception->getMessage());
                Assert::same($code, $exception->getCode());

                return $response;
            });

        return $responseFactory;
    }

    private function expectImageResponse(object $response, string $path): ResponseFactoryInterface
    {
        $responseFactory = Mockery::mock(ResponseFactoryInterface::class);

        $responseFactory->shouldReceive('createImageResponse')
            ->once()
            ->with(Mockery::type(FilesystemOperator::class), $path, Mockery::type(ConfigInterface::class))
            ->andReturn($response);

        return $responseFactory;
    }
}

(new LocalImageServerTest())->run();
