<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Resource;

use Intervention\Image\Image;
use Mockery;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifyResult;
use SixtyEightPublishers\ImageStorage\Resource\ImageResource;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ImageResourceTest extends TestCase
{
    public function testPathInfoShouldBeChanged(): void
    {
        $image = Mockery::mock(Image::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo1 = Mockery::mock(FilePathInfoInterface::class);
        $pathInfo2 = Mockery::mock(FilePathInfoInterface::class);

        $resource1 = new ImageResource($pathInfo1, $image, '/tmp/image.png', $modifierFacade);
        $resource2 = $resource1->withPathInfo($pathInfo2);

        Assert::notSame($resource1, $resource2);
        Assert::same($pathInfo1, $resource1->getPathInfo());
        Assert::same($pathInfo2, $resource2->getPathInfo());

        Assert::same('/tmp/image.png', $resource1->getLocalFilename());
        Assert::same('/tmp/image.png', $resource2->getLocalFilename());
    }

    public function testImageShouldBeModified(): void
    {
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $modifyResult = new ModifyResult(
            image: $modifiedImage,
            modified: true,
        );

        $modifierFacade->shouldReceive('modifyImage')
            ->once()
            ->with($image, $pathInfo, ['w' => 300])
            ->andReturn($modifyResult);

        $resource1 = new ImageResource($pathInfo, $image, '/tmp/image.png', $modifierFacade);
        $resource2 = $resource1->modifyImage(['w' => 300]);

        Assert::notSame($resource1, $resource2);
        Assert::same($image, $resource1->getSource());
        Assert::same($modifiedImage, $resource2->getSource());
        Assert::false($resource1->hasBeenModified());
        Assert::true($resource2->hasBeenModified());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ImageResourceTest())->run();
