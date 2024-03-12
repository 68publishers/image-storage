<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Resource;

use Intervention\Image\Image;
use Mockery;
use Nette\Utils\FileSystem;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Resource\TmpFile;
use SixtyEightPublishers\ImageStorage\Resource\TmpFileImageResource;
use Tester\Assert;
use Tester\TestCase;
use function file_exists;
use function md5;
use function sys_get_temp_dir;

require __DIR__ . '/../bootstrap.php';

final class TmpFileImageResourceTest extends TestCase
{
    public function testPathInfoShouldBeChanged(): void
    {
        $image = Mockery::mock(Image::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo1 = Mockery::mock(FilePathInfoInterface::class);
        $pathInfo2 = Mockery::mock(FilePathInfoInterface::class);

        $resource1 = new TmpFileImageResource($pathInfo1, $image, $modifierFacade, new TmpFile('fake'));
        $resource2 = $resource1->withPathInfo($pathInfo2);

        Assert::notSame($resource1, $resource2);
        Assert::same($pathInfo1, $resource1->getPathInfo());
        Assert::same($pathInfo2, $resource2->getPathInfo());
    }

    public function testImageShouldBeModified(): void
    {
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);

        $modifierFacade->shouldReceive('modifyImage')
            ->once()
            ->with($image, $pathInfo, ['w' => 300])
            ->andReturn($modifiedImage);

        $resource1 = new TmpFileImageResource($pathInfo, $image, $modifierFacade, new TmpFile('fake'));
        $resource2 = $resource1->modifyImage(['w' => 300]);

        Assert::notSame($resource1, $resource2);
        Assert::same($image, $resource1->getSource());
        Assert::same($modifiedImage, $resource2->getSource());
    }

    public function testTmpFileShouldBeUnlinked(): void
    {
        $image = Mockery::mock(Image::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);

        $filename = $this->createFile(__METHOD__);
        Assert::true(file_exists($filename));

        try {
            $resource = new TmpFileImageResource($pathInfo, $image, $modifierFacade, new TmpFile($filename));

            $resource->unlink();
            Assert::false(file_exists($filename));
        } finally {
            @unlink($filename);
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createFile(string $name): string
    {
        $filename = sys_get_temp_dir() . '/' . md5($name);
        FileSystem::write($filename, 'test');

        return $filename;
    }
}

(new TmpFileImageResourceTest())->run();
