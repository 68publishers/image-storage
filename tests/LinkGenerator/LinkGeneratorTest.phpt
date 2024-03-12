<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\LinkGenerator;

use Mockery;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGenerator;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class LinkGeneratorTest extends TestCase
{
    public function testExceptionShouldBeThrownIfFilePathInfoPassed(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory);

        Assert::exception(
            static fn () => $linkGenerator->link(Mockery::mock(FilePathInfoInterface::class)),
            InvalidArgumentException::class,
            'Path info passed into the method SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGenerator::link() must be an instance of SixtyEightPublishers\ImageStorage\PathInfoInterface.',
        );
    }

    public function testExceptionShouldBeThrownIfModifiersAreNull(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory);

        Assert::exception(
            static fn () => $linkGenerator->link($pathInfo),
            InvalidArgumentException::class,
            'Links to source images can not be created.',
        );
    }

    public function testLinkShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $pathInfo = $this->createPathInfo('images/w:100,h:200/image.png', null);

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory);

        Assert::null($linkGenerator->getSignatureStrategy());
        Assert::same('/images/w:100,h:200/image.png', $linkGenerator->link($pathInfo));
    }

    public function testVersionedLinkShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $pathInfo = $this->createPathInfo('images/w:100,h:200/image.png', '123');

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory);

        Assert::null($linkGenerator->getSignatureStrategy());
        Assert::same('/images/w:100,h:200/image.png?_v=123', $linkGenerator->link($pathInfo));
    }

    public function testSignedLinkShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);
        $pathInfo = $this->createPathInfo('images/w:100,h:200/image.png', null);

        $signatureStrategy->shouldReceive('createToken')
            ->once()
            ->with('images/w:100,h:200/image.png')
            ->andReturn('__token__');

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory, $signatureStrategy);

        Assert::same($signatureStrategy, $linkGenerator->getSignatureStrategy());
        Assert::same('/images/w:100,h:200/image.png?_s=__token__', $linkGenerator->link($pathInfo));
    }

    public function testVersionedAndSignedLinkShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $signatureStrategy = Mockery::mock(SignatureStrategyInterface::class);
        $pathInfo = $this->createPathInfo('images/w:100,h:200/image.png', '123');

        $signatureStrategy->shouldReceive('createToken')
            ->once()
            ->with('images/w:100,h:200/image.png')
            ->andReturn('__token__');

        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory, $signatureStrategy);

        Assert::same($signatureStrategy, $linkGenerator->getSignatureStrategy());
        Assert::same('/images/w:100,h:200/image.png?_v=123&_s=__token__', $linkGenerator->link($pathInfo));
    }

    public function testSrcSetShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $srcSetGeneratorFactory = Mockery::mock(SrcSetGeneratorFactoryInterface::class);
        $srcSetGenerator = Mockery::mock(SrcSetGenerator::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);
        $linkGenerator = new LinkGenerator(new Config([]), $modifierFacade, $srcSetGeneratorFactory);

        $srcSetGeneratorFactory->shouldReceive('create')
            ->once()
            ->with($linkGenerator, $modifierFacade)
            ->andReturn($srcSetGenerator);

        $srcSetGenerator->shouldReceive('generate')
            ->times(2)
            ->with($descriptor, $pathInfo)
            ->andReturn('srcset');

        Assert::same('srcset', $linkGenerator->srcSet($pathInfo, $descriptor));
        Assert::same('srcset', $linkGenerator->srcSet($pathInfo, $descriptor));
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    private function createPathInfo(string $path, ?string $version): PathInfoInterface
    {
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getPath')
            ->andReturn($path);

        $pathInfo->shouldReceive('getVersion')
            ->andReturn($version);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        return $pathInfo;
    }
}

(new LinkGeneratorTest())->run();
