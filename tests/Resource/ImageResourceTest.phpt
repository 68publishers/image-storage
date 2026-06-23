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
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

require __DIR__ . '/../bootstrap.php';

final class ImageResourceTest extends TestCase
{
    public function testPathInfoShouldBeChanged(): void
    {
        $image = Mockery::mock(Image::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo1 = Mockery::mock(FilePathInfoInterface::class);
        $pathInfo2 = Mockery::mock(FilePathInfoInterface::class);

        $resource1 = new ImageResource($pathInfo1, $image, '/tmp/image.png', $modifierFacade, 90);
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
            encodeFormat: null,
            encodeQuality: null,
        );

        $modifierFacade->shouldReceive('modifyImage')
            ->once()
            ->with($image, $pathInfo, ['w' => 300], false)
            ->andReturn($modifyResult);

        $resource1 = new ImageResource($pathInfo, $image, '/tmp/image.png', $modifierFacade, 90);
        $resource2 = $resource1->modifyImage(['w' => 300]);

        Assert::notSame($resource1, $resource2);
        Assert::same($image, $resource1->getSource());
        Assert::same($modifiedImage, $resource2->getSource());
        Assert::false($resource1->hasBeenModified());
        Assert::true($resource2->hasBeenModified());
    }

    public function testEncodedImageShouldBeReadFromTheLocalFileWhenNotModified(): void
    {
        $localFile = (string) tempnam(sys_get_temp_dir(), '68publishers.ImageStorage');
        file_put_contents($localFile, '... original bytes ...');

        try {
            $image = Mockery::mock(Image::class);
            $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
            $pathInfo = Mockery::mock(FilePathInfoInterface::class);

            # an unmodified image is not re-encoded - the stored bytes are the original local file
            $resource = new ImageResource($pathInfo, $image, $localFile, $modifierFacade, 90);

            Assert::same('... original bytes ...', $resource->getEncodedImage());
            # the result is cached
            Assert::same('... original bytes ...', $resource->getEncodedImage());
        } finally {
            @unlink($localFile);
        }
    }

    public function testModifiedImageShouldBeEncodedWithTheConfiguredQuality(): void
    {
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $encodedImage = Mockery::mock(Image::class);

        $modifierFacade->shouldReceive('modifyImage')
            ->once()
            ->with($image, $pathInfo, ['w' => 300], false)
            ->andReturn(new ModifyResult(
                image: $modifiedImage,
                modified: true,
                encodeFormat: null,
                encodeQuality: null,
            ));

        $modifiedImage->shouldReceive('encode')
            ->once()
            ->with('', 90)
            ->andReturn($encodedImage);

        $encodedImage->shouldReceive('getEncoded')
            ->once()
            ->withNoArgs()
            ->andReturn('... encoded bytes ...');

        $resource = new ImageResource($pathInfo, $image, '/tmp/image.png', $modifierFacade, 90);
        $resource = $resource->modifyImage(['w' => 300]);

        Assert::same('... encoded bytes ...', $resource->getEncodedImage());
        # the result is cached (encode() / getEncoded() are expected exactly once)
        Assert::same('... encoded bytes ...', $resource->getEncodedImage());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ImageResourceTest())->run();
