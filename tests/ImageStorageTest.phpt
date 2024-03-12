<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests;

use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\RequestInterface;
use SixtyEightPublishers\ImageStorage\ImageStorage;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

final class ImageStorageTest extends TestCase
{
    public function testPathInfoShouldBeCreated(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $pathInfo1 = Mockery::mock(ImagePathInfoInterface::class);
        $pathInfo2 = Mockery::mock(ImagePathInfoInterface::class);
        $pathInfo3 = Mockery::mock(ImagePathInfoInterface::class);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('var/www/file1.png', null)
            ->andReturn($pathInfo1);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('var/www/file2.png', 'preset')
            ->andReturn($pathInfo2);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('var/www/file3.png', ['w' => 15, 'h' => 15])
            ->andReturn($pathInfo3);

        $storage = $this->createImageStorage(infoFactory: $infoFactory);

        Assert::same($pathInfo1, $storage->createPathInfo('var/www/file1.png'));
        Assert::same($pathInfo2, $storage->createPathInfo('var/www/file2.png', 'preset'));
        Assert::same($pathInfo3, $storage->createPathInfo('var/www/file3.png', ['w' => 15, 'h' => 15]));
    }

    public function testPathInfoShouldBeCreatedFromEmptyPath(): void
    {
        $noImageResolver = Mockery::mock(NoImageResolverInterface::class);
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $noImagePathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $pathInfo1 = Mockery::mock(ImagePathInfoInterface::class);
        $pathInfo2 = Mockery::mock(ImagePathInfoInterface::class);
        $pathInfo3 = Mockery::mock(ImagePathInfoInterface::class);

        $noImageResolver->shouldReceive('getNoImage')
            ->times(3)
            ->with(null)
            ->andReturn($noImagePathInfo);

        $noImagePathInfo->shouldReceive('withModifiers')
            ->once()
            ->with(null)
            ->andReturn($pathInfo1);

        $noImagePathInfo->shouldReceive('withModifiers')
            ->once()
            ->with('preset')
            ->andReturn($pathInfo2);

        $noImagePathInfo->shouldReceive('withModifiers')
            ->once()
            ->with(['w' => 15, 'h' => 15])
            ->andReturn($pathInfo3);

        $storage = $this->createImageStorage(noImageResolver: $noImageResolver, infoFactory: $infoFactory);

        Assert::same($pathInfo1, $storage->createPathInfo(''));
        Assert::same($pathInfo2, $storage->createPathInfo('', 'preset'));
        Assert::same($pathInfo3, $storage->createPathInfo('', ['w' => 15, 'h' => 15]));
    }

    public function testFileInfoShouldBeCreatedFromImagePathInfo(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $fileInfo = Mockery::mock(FileInfoInterface::class);

        $infoFactory->shouldReceive('createFileInfo')
            ->once()
            ->with($pathInfo)
            ->andReturn($fileInfo);

        $storage = $this->createImageStorage(infoFactory: $infoFactory);

        Assert::same($fileInfo, $storage->createFileInfo($pathInfo));
    }

    public function testFileInfoShouldBeCreatedFromFilePathInfo(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $filePathInfo = Mockery::mock(FilePathInfoInterface::class);
        $imagePathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $fileInfo = Mockery::mock(FileInfoInterface::class);

        $filePathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file.png');

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('var/www/file.png', null)
            ->andReturn($imagePathInfo);

        $infoFactory->shouldReceive('createFileInfo')
            ->once()
            ->with($imagePathInfo)
            ->andReturn($fileInfo);

        $storage = $this->createImageStorage(infoFactory: $infoFactory);

        Assert::same($fileInfo, $storage->createFileInfo($filePathInfo));
    }

    public function testImageResponseShouldBeReturned(): void
    {
        $imageServerFactory = Mockery::mock(ImageServerFactoryInterface::class);
        $imageServer = Mockery::mock(ImageServerInterface::class);
        $request1 = Mockery::mock(RequestInterface::class);
        $request2 = Mockery::mock(RequestInterface::class);
        $response1 = (object) ['status' => 200];
        $response2 = (object) ['status' => 200];

        $imageStorage = $this->createImageStorage(imageServerFactory: $imageServerFactory);

        $imageServerFactory->shouldReceive('create')
            ->once()
            ->with($imageStorage)
            ->andReturn($imageServer);

        $imageServer->shouldReceive('getImageResponse')
            ->once()
            ->with($request1)
            ->andReturn($response1);

        $imageServer->shouldReceive('getImageResponse')
            ->once()
            ->with($request2)
            ->andReturn($response2);

        Assert::same($response1, $imageStorage->getImageResponse($request1));
        Assert::same($response2, $imageStorage->getImageResponse($request2));
    }

    public function testNoImageConfigShouldBeReturned(): void
    {
        $noImageResolver = Mockery::mock(NoImageResolverInterface::class);
        $noImageConfig = Mockery::mock(NoImageConfigInterface::class);

        $noImageResolver->shouldReceive('getNoImageConfig')
            ->once()
            ->withNoArgs()
            ->andReturn($noImageConfig);

        $imageStorage = $this->createImageStorage(noImageResolver: $noImageResolver);

        Assert::same($noImageConfig, $imageStorage->getNoImageConfig());
    }

    public function testNoImageShouldBeReturned(): void
    {
        $noImageResolver = Mockery::mock(NoImageResolverInterface::class);
        $defaultNoImage = Mockery::mock(ImagePathInfoInterface::class);
        $namedNoImage = Mockery::mock(ImagePathInfoInterface::class);

        $noImageResolver->shouldReceive('getNoImage')
            ->once()
            ->with(null)
            ->andReturn($defaultNoImage);

        $noImageResolver->shouldReceive('getNoImage')
            ->once()
            ->with('name')
            ->andReturn($namedNoImage);

        $imageStorage = $this->createImageStorage(noImageResolver: $noImageResolver);

        Assert::same($defaultNoImage, $imageStorage->getNoImage());
        Assert::same($namedNoImage, $imageStorage->getNoImage('name'));
    }

    public function testIsNoImageShouldBeReturned(): void
    {
        $noImageResolver = Mockery::mock(NoImageResolverInterface::class);

        $noImageResolver->shouldReceive('isNoImage')
            ->once()
            ->with('var/www/noImage.png')
            ->andReturn(true);

        $imageStorage = $this->createImageStorage(noImageResolver: $noImageResolver);

        Assert::true($imageStorage->isNoImage('var/www/noImage.png'));
    }

    public function testNoImageShouldBeResolved(): void
    {
        $noImageResolver = Mockery::mock(NoImageResolverInterface::class);
        $noImagePathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $noImageResolver->shouldReceive('resolveNoImage')
            ->once()
            ->with('var/www/file.png')
            ->andReturn($noImagePathInfo);

        $imageStorage = $this->createImageStorage(noImageResolver: $noImageResolver);

        Assert::same($noImagePathInfo, $imageStorage->resolveNoImage('var/www/file.png'));
    }

    public function testSrcSetShouldBeReturned(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);

        $linkGenerator->shouldReceive('srcSet')
            ->once()
            ->with($pathInfo, $descriptor)
            ->andReturn('srcset');

        $imageStorage = $this->createImageStorage(linkGenerator: $linkGenerator);

        Assert::same('srcset', $imageStorage->srcSet($pathInfo, $descriptor));
    }

    public function testSignatureStrategyShouldBeReturned(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);

        $linkGenerator->shouldReceive('getSignatureStrategy')
            ->once()
            ->withNoArgs()
            ->andReturn($signatureStrategy);

        $imageStorage = $this->createImageStorage(linkGenerator: $linkGenerator);

        Assert::same($signatureStrategy, $imageStorage->getSignatureStrategy());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createImageStorage(
        ?ConfigInterface $config = null,
        ?ResourceFactoryInterface $resourceFactory = null,
        ?LinkGeneratorInterface $linkGenerator = null,
        ?ImagePersisterInterface $imagePersister = null,
        ?NoImageResolverInterface $noImageResolver = null,
        ?InfoFactoryInterface $infoFactory = null,
        ?ImageServerFactoryInterface $imageServerFactory = null,
    ): ImageStorage {
        $config = $config ?? Mockery::mock(ConfigInterface::class);
        $resourceFactory = $resourceFactory ?? Mockery::mock(ResourceFactoryInterface::class);
        $linkGenerator = $linkGenerator ?? Mockery::mock(LinkGeneratorInterface::class);
        $imagePersister = $imagePersister ?? Mockery::mock(ImagePersisterInterface::class);
        $noImageResolver = $noImageResolver ?? Mockery::mock(NoImageResolverInterface::class);
        $infoFactory = $infoFactory ?? Mockery::mock(InfoFactoryInterface::class);
        $imageServerFactory = $imageServerFactory ?? Mockery::mock(ImageServerFactoryInterface::class);

        return new ImageStorage('default', $config, $resourceFactory, $linkGenerator, $imagePersister, $noImageResolver, $infoFactory, $imageServerFactory);
    }
}

(new ImageStorageTest())->run();
