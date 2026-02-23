<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests;

use Mockery;
use SixtyEightPublishers\FileStorage\Exception\PathInfoException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\PathInfo;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

final class PathInfoTest extends TestCase
{
    public function testExceptionShouldBeThrownIfInvalidExtensionPassedIntoConstructor(): void
    {
        $codec = Mockery::mock(CodecInterface::class);

        Assert::exception(
            static fn () => new PathInfo($codec, 'var/www', 'image', 'json'),
            PathInfoException::class,
            'File extension .json is not supported.',
        );
    }

    public function testPathInfoShouldBeCreated(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $info1 = new PathInfo($codec, 'var/www', 'image', null);
        $info2 = new PathInfo($codec, 'var/www', 'image', 'png', 'preset');
        $info3 = new PathInfo($codec, 'var/www', 'image', 'png', ['w' => 15, 'h' => 15]);
        $info4 = new PathInfo($codec, 'var/www', 'image', 'png', null, '123');

        Assert::same('var/www', $info1->getNamespace());
        Assert::same('var/www', $info2->getNamespace());
        Assert::same('var/www', $info3->getNamespace());
        Assert::same('var/www', $info4->getNamespace());

        Assert::same('image', $info1->getName());
        Assert::same('image', $info2->getName());
        Assert::same('image', $info3->getName());
        Assert::same('image', $info4->getName());

        Assert::null($info1->getExtension());
        Assert::same('png', $info2->getExtension());
        Assert::same('png', $info3->getExtension());
        Assert::same('png', $info4->getExtension());

        Assert::null($info1->getModifiers());
        Assert::same('preset', $info2->getModifiers());
        Assert::same(['w' => 15, 'h' => 15], $info3->getModifiers());
        Assert::null($info4->getModifiers());

        Assert::null($info1->getVersion());
        Assert::null($info2->getVersion());
        Assert::null($info3->getVersion());
        Assert::same('123', $info4->getVersion());
    }

    public function testExceptionShouldBeThrownIfExtensionChangedWithInvalidValue(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $info = new PathInfo($codec, 'var/www', 'image', null);

        Assert::exception(
            static fn () =>$info->withExtension('txt'),
            PathInfoException::class,
            'File extension .txt is not supported.',
        );

        Assert::exception(
            static fn () =>$info->withExt('so'),
            PathInfoException::class,
            'File extension .so is not supported.',
        );
    }

    public function testExtensionShouldBeChanged(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $info1 = new PathInfo($codec, 'var/www', 'image', null);
        $info2 = $info1->withExtension('png');
        $info3 = $info1->withExt('jpg');

        Assert::notSame($info1, $info2);
        Assert::notSame($info1, $info3);
        Assert::null($info1->getExtension());
        Assert::same('png', $info2->getExtension());
        Assert::same('jpg', $info3->getExtension());
    }

    public function testModifiersShouldBeChanged(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $info1 = new PathInfo($codec, 'var/www', 'image', 'png', null);
        $info2 = $info1->withModifiers('preset');
        $info3 = $info1->withModifiers(['w' => 15, 'h' => 15]);

        Assert::notSame($info1, $info2);
        Assert::notSame($info1, $info3);
        Assert::null($info1->getModifiers());
        Assert::same('preset', $info2->getModifiers());
        Assert::same(['w' => 15, 'h' => 15], $info3->getModifiers());
    }

    public function testEncodedModifiersShouldBeChanged(): void
    {
        $codec = Mockery::mock(CodecInterface::class);

        $codec->shouldReceive('pathToModifiers')
            ->once()
            ->with('w:15,h:15')
            ->andReturn([
                'w' => 15,
                'h' => 15,
            ]);

        $info1 = new PathInfo($codec, 'var/www', 'image', 'png', null);
        $info2 = $info1->withEncodedModifiers('w:15,h:15');

        Assert::notSame($info1, $info2);
        Assert::null($info1->getModifiers());
        Assert::same(['w' => 15, 'h' => 15], $info2->getModifiers());
    }

    public function testSourcePathShouldBeReturned(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $info1 = new PathInfo($codec, 'var/www', 'image', null, null);
        $info2 = new PathInfo($codec, 'var/www', 'image', 'png', null);
        $info3 = new PathInfo($codec, '', 'image', null, null);
        $info4 = new PathInfo($codec, '', 'image', 'gif', null);

        Assert::same('var/www/image', $info1->getPath());
        Assert::same('var/www/image', $info2->getPath());
        Assert::same('image', $info3->getPath());
        Assert::same('image', $info4->getPath());
    }

    public function testModifiedPathShouldBeReturned(): void
    {
        $codecPreset = Mockery::mock(CodecInterface::class);
        $codecArray = Mockery::mock(CodecInterface::class);

        $codecPreset->shouldReceive('modifiersToPath')
            ->times(4)
            ->with('preset')
            ->andReturn('w:15,h:15');

        $codecArray->shouldReceive('modifiersToPath')
            ->times(4)
            ->with(['h' => 15, 'w' => 15])
            ->andReturn('w:15,h:15');

        $infoPreset1 = new PathInfo($codecPreset, 'var/www', 'image', null, 'preset');
        $infoPreset2 = new PathInfo($codecPreset, 'var/www', 'image', 'png', 'preset');
        $infoPreset3 = new PathInfo($codecPreset, '', 'image', null, 'preset');
        $infoPreset4 = new PathInfo($codecPreset, '', 'image', 'gif', 'preset');

        $infoArray1 = new PathInfo($codecArray, 'var/www', 'image', null, ['h' => 15, 'w' => 15]);
        $infoArray2 = new PathInfo($codecArray, 'var/www', 'image', 'png', ['h' => 15, 'w' => 15]);
        $infoArray3 = new PathInfo($codecArray, '', 'image', null, ['h' => 15, 'w' => 15]);
        $infoArray4 = new PathInfo($codecArray, '', 'image', 'gif', ['h' => 15, 'w' => 15]);

        Assert::same('var/www/w:15,h:15/image.jpg', $infoPreset1->getPath());
        Assert::same('var/www/w:15,h:15/image.png', $infoPreset2->getPath());
        Assert::same('w:15,h:15/image.jpg', $infoPreset3->getPath());
        Assert::same('w:15,h:15/image.gif', $infoPreset4->getPath());

        Assert::same('var/www/w:15,h:15/image.jpg', $infoArray1->getPath());
        Assert::same('var/www/w:15,h:15/image.png', $infoArray2->getPath());
        Assert::same('w:15,h:15/image.jpg', $infoArray3->getPath());
        Assert::same('w:15,h:15/image.gif', $infoArray4->getPath());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new PathInfoTest())->run();
