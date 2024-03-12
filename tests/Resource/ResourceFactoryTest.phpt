<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Resource;

use Exception;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException as LeagueFilesystemExceptionInterface;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToReadFile;
use Mockery;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Resource\ImageResource;
use SixtyEightPublishers\ImageStorage\Resource\ResourceFactory;
use SixtyEightPublishers\ImageStorage\Resource\TmpFileImageResource;
use Tester\Assert;
use Tester\TestCase;
use function file_exists;
use function file_get_contents;

require __DIR__ . '/../bootstrap.php';

final class ResourceFactoryTest extends TestCase
{
    public function testExceptionShouldBeThrownIfFileNotExists(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $resourceFactory = new ResourceFactory($this->createFilesystem(), $imageManager, $modifierFacade);

        Assert::exception(
            static fn () => $resourceFactory->createResource($pathInfo),
            FileNotFoundException::class,
            'File "var/www/file" not found.',
        );
    }

    public function testExceptionShouldBeThrownIfFilesystemIsUnableToRead(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $filesystem = $this->createFilesystem([
            'var/www/file' => '... image content ...',
        ]);
        $filesystem = Mockery::instanceMock($filesystem);

        $filesystem->shouldReceive('read')
            ->once()
            ->with('source://var/www/file')
            ->andThrows(UnableToReadFile::fromLocation('var/www/file', 'test'));

        $resourceFactory = new ResourceFactory($filesystem, $imageManager, $modifierFacade);

        Assert::exception(
            static fn () => $resourceFactory->createResource($pathInfo),
            FilesystemException::class,
            'Unable to read file "var/www/file".',
        );
    }

    public function testExceptionShouldBeThrownISomeFilesystemExceptionIsThrown(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $filesystem = $this->createFilesystem([
            'var/www/file' => '... image content ...',
        ]);
        $filesystem = Mockery::instanceMock($filesystem);

        $filesystem->shouldReceive('read')
            ->once()
            ->with('source://var/www/file')
            ->andThrows(new class('test') extends Exception implements LeagueFilesystemExceptionInterface {
            });

        $resourceFactory = new ResourceFactory($filesystem, $imageManager, $modifierFacade);

        Assert::exception(
            static fn () => $resourceFactory->createResource($pathInfo),
            FilesystemException::class,
            'test',
        );
    }

    public function testResourceShouldBeCreatedFromFilePathInfo(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);
        $image = Mockery::mock(Image::class);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $imageManager->shouldReceive('make')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnUsing(static function (string $filename) use ($image) {
                Assert::true(file_exists($filename));
                Assert::same('... image content ...', file_get_contents($filename));

                return $image;
            });

        $filesystem = $this->createFilesystem([
            'var/www/file' => '... image content ...',
        ]);

        $resourceFactory = new ResourceFactory($filesystem, $imageManager, $modifierFacade);
        $resource = $resourceFactory->createResource($pathInfo);

        Assert::type(TmpFileImageResource::class, $resource);
        Assert::same($pathInfo, $resource->getPathInfo());
        Assert::same($image, $resource->getSource());
    }

    public function testResourceShouldBeCreatedFromImagePathInfoWithoutModifiers(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $image = Mockery::mock(Image::class);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $imageManager->shouldReceive('make')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnUsing(static function (string $filename) use ($image) {
                Assert::true(file_exists($filename));
                Assert::same('... image content ...', file_get_contents($filename));

                return $image;
            });

        $filesystem = $this->createFilesystem([
            'var/www/file' => '... image content ...',
        ]);

        $resourceFactory = new ResourceFactory($filesystem, $imageManager, $modifierFacade);
        $resource = $resourceFactory->createResource($pathInfo);

        Assert::type(TmpFileImageResource::class, $resource);
        Assert::same($pathInfo, $resource->getPathInfo());
        Assert::same($image, $resource->getSource());
    }

    public function testResourceShouldBeCreatedFromImagePathInfoWithModifiers(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $modifiedPathInfo = Mockery::mock(ImagePathInfoInterface::class);
        $image = Mockery::mock(Image::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 150]);

        $pathInfo->shouldReceive('withModifiers')
            ->once()
            ->with(null)
            ->andReturn($modifiedPathInfo);

        $modifiedPathInfo->shouldReceive('getPath')
            ->once()
            ->withNoArgs()
            ->andReturn('var/www/file');

        $imageManager->shouldReceive('make')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnUsing(static function (string $filename) use ($image) {
                Assert::true(file_exists($filename));
                Assert::same('... image content ...', file_get_contents($filename));

                return $image;
            });

        $filesystem = $this->createFilesystem([
            'var/www/file' => '... image content ...',
        ]);

        $resourceFactory = new ResourceFactory($filesystem, $imageManager, $modifierFacade);
        $resource = $resourceFactory->createResource($pathInfo);

        Assert::type(TmpFileImageResource::class, $resource);
        Assert::same($pathInfo, $resource->getPathInfo());
        Assert::same($image, $resource->getSource());
    }

    public function testResourceShouldBeCreatedFromLocalFile(): void
    {
        $imageManager = Mockery::mock(ImageManager::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);
        $image = Mockery::mock(Image::class);

        $imageManager->shouldReceive('make')
            ->once()
            ->with('filename')
            ->andReturn($image);

        $resourceFactory = new ResourceFactory($this->createFilesystem(), $imageManager, $modifierFacade);
        $resource = $resourceFactory->createResourceFromFile($pathInfo, 'filename');

        Assert::type(ImageResource::class, $resource);
        Assert::same($pathInfo, $resource->getPathInfo());
        Assert::same($image, $resource->getSource());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createFilesystem(array $files = []): MountManager
    {
        $fs = new Filesystem(
            new InMemoryFilesystemAdapter(),
        );

        foreach ($files as $filename => $content) {
            $fs->write($filename, $content);
        }

        return new MountManager([
            'source' => $fs,
        ]);
    }
}

(new ResourceFactoryTest())->run();
