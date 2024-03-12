<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Info;

use Mockery;
use SixtyEightPublishers\ImageStorage\Info\InfoFactory;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class InfoFactoryTest extends TestCase
{
    public function testPathInfoShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $codec = Mockery::mock(CodecInterface::class);

        $modifierFacade->shouldReceive('getCodec')
            ->withNoArgs()
            ->andReturn($codec);

        $infoFactory = new InfoFactory($modifierFacade, $linkGenerator, 'default');
        $pathInfo1 = $infoFactory->createPathInfo('var/www/file.png');
        $pathInfo2 = $infoFactory->createPathInfo('file.png', 'preset');
        $pathInfo3 = $infoFactory->createPathInfo('file', ['w' => 300]);

        Assert::same('var/www', $pathInfo1->getNamespace());
        Assert::same('', $pathInfo2->getNamespace());
        Assert::same('', $pathInfo3->getNamespace());

        Assert::same('file', $pathInfo1->getName());
        Assert::same('file', $pathInfo2->getName());
        Assert::same('file', $pathInfo3->getName());

        Assert::same('png', $pathInfo1->getExtension());
        Assert::same('png', $pathInfo2->getExtension());
        Assert::null($pathInfo3->getExtension());

        Assert::null($pathInfo1->getModifiers());
        Assert::same('preset', $pathInfo2->getModifiers());
        Assert::same(['w' => 300], $pathInfo3->getModifiers());
    }

    public function testFileInfoShouldBeCreated(): void
    {
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $infoFactory = new InfoFactory($modifierFacade, $linkGenerator, 'default');
        $fileInfo = $infoFactory->createFileInfo($pathInfo);

        Assert::same('default', $fileInfo->getStorageName());
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}

(new InfoFactoryTest())->run();
