<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\NoImage;

use Mockery;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfig;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolver;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class NoImageResolverTest extends TestCase
{
    public function testNoImageConfigShouldBeReturned(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig(null, [], []);
        $resolver = new NoImageResolver($infoFactory, $config);

        Assert::same($config, $resolver->getNoImageConfig());
    }

    public function testExceptionShouldBeThrownIfDefaultNoImageIsNotDefined(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig(null, [], []);
        $resolver = new NoImageResolver($infoFactory, $config);

        Assert::exception(
            static fn () => $resolver->getNoImage(),
            InvalidArgumentException::class,
            'Default no-image path is not defined.',
        );
    }

    public function testExceptionShouldBeThrownIfNamedNoImageIsNotDefined(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig(null, [], []);
        $resolver = new NoImageResolver($infoFactory, $config);

        Assert::exception(
            static fn () => $resolver->getNoImage('test'),
            InvalidArgumentException::class,
            'No-image with name "test" is not defined.',
        );
    }

    public function testDefaultNoImageShouldBeReturned(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig('default/noimage.png', [], []);
        $noImage = Mockery::mock(PathInfoInterface::class);
        $resolver = new NoImageResolver($infoFactory, $config);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('default/noimage.png')
            ->andReturn($noImage);

        Assert::same($noImage, $resolver->getNoImage());
    }

    public function testNamedNoImageShouldBeReturned(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig(null, ['test' => 'test/noimage.png'], []);
        $noImage = Mockery::mock(PathInfoInterface::class);
        $resolver = new NoImageResolver($infoFactory, $config);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('test/noimage.png')
            ->andReturn($noImage);

        Assert::same($noImage, $resolver->getNoImage('test'));
    }

    public function testIsNoImageMethodShouldReturnCorrectResults(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig('default/noimage.png', ['test' => 'test/noimage.png'], []);
        $resolver = new NoImageResolver($infoFactory, $config);

        Assert::true($resolver->isNoImage('default/noimage.png'));
        Assert::true($resolver->isNoImage('test/noimage.png'));
        Assert::false($resolver->isNoImage('test2/noimage.png'));
    }

    public function testNoImageShouldBeResolved(): void
    {
        $infoFactory = Mockery::mock(InfoFactoryInterface::class);
        $config = new NoImageConfig('default/noimage.png', [
            'test' => 'test/noimage.png',
        ], [
            'test' => '^test\/',
        ]);
        $defaultNoImage = Mockery::mock(PathInfoInterface::class);
        $testNoImage = Mockery::mock(PathInfoInterface::class);
        $resolver = new NoImageResolver($infoFactory, $config);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('default/noimage.png')
            ->andReturn($defaultNoImage);

        $infoFactory->shouldReceive('createPathInfo')
            ->once()
            ->with('test/noimage.png')
            ->andReturn($testNoImage);

        Assert::same($defaultNoImage, $resolver->resolveNoImage('static/w:100/image.png'));
        Assert::same($testNoImage, $resolver->resolveNoImage('test/w:100/image.png'));
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}

(new NoImageResolverTest())->run();
