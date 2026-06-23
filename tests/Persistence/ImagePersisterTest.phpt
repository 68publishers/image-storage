<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Persistence;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Mockery;
use SixtyEightPublishers\FileStorage\Exception\FilesystemException;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\FileStorage\Persistence\FilePersisterInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersister;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Resource\ResourceInterface as ImageResourceInterface;
use SixtyEightPublishers\ImageStorage\Resource\TmpFileImageResource;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ImagePersisterTest extends TestCase
{
    public function testFilesystemShouldBeReturned(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        Assert::type(FilesystemOperator::class, $persister->getFilesystem());
    }

    public function testExceptionShouldBeThrownIfExistenceIsCheckedWithFilePathInfo(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        Assert::exception(
            static fn () => $persister->exists(Mockery::mock(FilePathInfoInterface::class)),
            InvalidArgumentException::class,
            'Path info passed into the method SixtyEightPublishers\ImageStorage\Persistence\ImagePersister::exists() must be an instance of SixtyEightPublishers\ImageStorage\PathInfoInterface.',
        );
    }

    public function testSourcePathShouldExists(): void
    {
        $persister = new ImagePersister(
            $this->createFilesystem([
                'path/image' => '... image content ...',
            ]),
        );

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->andReturn('path/image');

        Assert::true($persister->exists($pathInfo));
    }

    public function testCachedPathShouldExists(): void
    {
        $persister = new ImagePersister(
            $this->createFilesystem([], [
                'path/w:100,h:200/image.png' => '... image content ...',
            ]),
        );

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->andReturn('path/w:100,h:200/image.png');

        Assert::true($persister->exists($pathInfo));
    }

    public function testSourcePathShouldNotExists(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->andReturn('path/image');

        Assert::false($persister->exists($pathInfo));
    }

    public function testCachedPathShouldNotExists(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->andReturn('path/w:100,h:200/image.png');

        Assert::false($persister->exists($pathInfo));
    }

    public function testPathShouldNotExistsOnFilesystemException(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem());
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->once()
            ->andReturn('path/image');

        $filesystem->shouldReceive('fileExists')
            ->once()
            ->with('source://path/image')
            ->andThrows(UnableToReadFile::fromLocation('source://path/image', 'test'));

        Assert::false($persister->exists($pathInfo));
    }

    public function testExceptionShouldBeThrownIfResourceIsSavedWithFilePathInfo(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(FilePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        Assert::exception(
            static fn () => $persister->save($resource),
            InvalidArgumentException::class,
            'Path info passed into the method SixtyEightPublishers\ImageStorage\Persistence\ImagePersister::save() must be an instance of SixtyEightPublishers\ImageStorage\PathInfoInterface.',
        );
    }

    public function testNewSourceShouldBeSaved(): void
    {
        $filesystem = $this->createFilesystem();
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... image content ...');

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->andReturn('path/image');

        Assert::same('path/image', $persister->save($resource));
        Assert::true($filesystem->fileExists('source://path/image'));
        Assert::same('... image content ...', $filesystem->read('source://path/image'));
    }

    public function testSourceImageShouldBeUpdatedAndCachedImagesShouldBeDeleted(): void
    {
        $filesystem = $this->createFilesystem([
            'path/image' => '... image content ...',
        ], [
            'path/w:100/image.png' => '... image content ...',
            'path/w:100,pd:2/image.png' => '... image content ...',
        ]);
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... new image content ...');

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('path');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        Assert::same('path/image', $persister->save($resource));
        Assert::true($filesystem->fileExists('source://path/image'));
        Assert::same('... new image content ...', $filesystem->read('source://path/image'));
        Assert::false($filesystem->fileExists('cache://path/w:100/image.png'));
        Assert::false($filesystem->fileExists('cache://path/w:100,pd:2/image.png'));
    }

    public function testTempFileShouldBeUnlinkedAfterSave(): void
    {
        $filesystem = $this->createFilesystem();
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(TmpFileImageResource::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... image content ...');

        $resource->shouldReceive('unlink')
            ->once()
            ->withNoArgs()
            ->andReturns();

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->andReturn('path/image');

        Assert::same('path/image', $persister->save($resource));
        Assert::true($filesystem->fileExists('source://path/image'));
    }

    public function testCachedImageShouldBeSaved(): void
    {
        $filesystem = $this->createFilesystem();
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->andReturn('path/w:100,h:200/image.png');

        # a cached image is re-modified before it is encoded and written
        $resource->shouldReceive('modifyImage')
            ->once()
            ->with(['w' => 100, 'h' => 200], true)
            ->andReturnSelf();

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... image content ...');

        Assert::same('path/w:100,h:200/image.png', $persister->save($resource));
        Assert::true($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
        Assert::same('... image content ...', $filesystem->read('cache://path/w:100,h:200/image.png'));
    }

    public function testExceptionShouldBeThrownIfFilesystemThrownExceptionOnSave(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem());
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... image content ...');

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->andReturn('path/image');

        $filesystem->shouldReceive('write')
            ->once()
            ->with('source://path/image', '... image content ...', [])
            ->andThrows(UnableToWriteFile::atLocation('source://path/image', 'test'));

        Assert::exception(
            static fn () => $persister->save($resource),
            FilesystemException::class,
            'Unable to write file at location: source://path/image. test',
        );
    }

    public function testExceptionShouldNotBeThrownIfFilesystemThrownExceptionOnSaveButExceptionAreSuppressed(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem());
        $persister = new ImagePersister($filesystem);

        $resource = Mockery::mock(ImageResourceInterface::class);
        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $resource->shouldReceive('getPathInfo')
            ->once()
            ->withNoArgs()
            ->andReturn($pathInfo);

        $resource->shouldReceive('getEncodedImage')
            ->once()
            ->withNoArgs()
            ->andReturn('... image content ...');

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->andReturn('path/image');

        $filesystem->shouldReceive('write')
            ->once()
            ->with('source://path/image', '... image content ...', [
                FilePersisterInterface::OPTION_SUPPRESS_EXCEPTIONS => true,
            ])
            ->andThrows(UnableToWriteFile::atLocation('source://path/image', 'test'));

        Assert::same('path/image', $persister->save($resource, [
            FilePersisterInterface::OPTION_SUPPRESS_EXCEPTIONS => true,
        ]));
        Assert::false($filesystem->fileExists('source://path/image'));
    }

    public function testExceptionShouldBeThrownIfFilePathInfoIsDeleted(): void
    {
        $persister = new ImagePersister($this->createFilesystem());

        Assert::exception(
            static fn () => $persister->delete(Mockery::mock(FilePathInfoInterface::class)),
            InvalidArgumentException::class,
            'Path info passed into the method SixtyEightPublishers\ImageStorage\Persistence\ImagePersister::delete() must be an instance of SixtyEightPublishers\ImageStorage\PathInfoInterface.',
        );
    }

    public function testCachedImageShouldBeDeleted(): void
    {
        $filesystem = $this->createFilesystem([], [
            'path/w:100,h:200/image.png' => '... image content ...',
            'path/w:50,h:100/image.png' => '... image content ...',
        ]);
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/w:100,h:200/image.png');

        $persister->delete($pathInfo);

        Assert::false($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
        Assert::true($filesystem->fileExists('cache://path/w:50,h:100/image.png'));
    }

    public function testExceptionShouldBeThrownIfFilesystemThrownExceptionWhenDeleting(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem([], [
            'path/w:100,h:200/image.png' => '... image content ...',
        ]));
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/w:100,h:200/image.png');

        $filesystem->shouldReceive('delete')
            ->once()
            ->with('cache://path/w:100,h:200/image.png')
            ->andThrows(UnableToDeleteFile::atLocation('cache://path/w:100,h:200/image.png', 'test'));

        Assert::exception(
            static fn () => $persister->delete($pathInfo),
            FilesystemException::class,
            'Unable to delete file located at: cache://path/w:100,h:200/image.png. test',
        );
    }

    public function testExceptionShouldNotBeThrownIfFilesystemThrownExceptionWhenDeletingButExceptionAreSuppressed(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem([], [
            'path/w:100,h:200/image.png' => '... image content ...',
        ]));
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(['w' => 100, 'h' => 200]);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/w:100,h:200/image.png');

        $filesystem->shouldReceive('delete')
            ->once()
            ->with('cache://path/w:100,h:200/image.png')
            ->andThrows(UnableToDeleteFile::atLocation('cache://path/w:100,h:200/image.png', 'test'));

        $persister->delete($pathInfo, [
            FilePersisterInterface::OPTION_SUPPRESS_EXCEPTIONS => true,
        ]);

        Assert::true($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
    }

    public function testExceptionShouldBeThrownIfFilesystemThrownExceptionOnCachedImagesListingForDeletion(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem([
            'path/image' => '... image content ...',
        ], [
            'path/w:100,h:200/image.png' => '... image content ...',
            'path/w:50,h:100/image.png' => '... image content ...',
        ]));
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('path');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        $filesystem->shouldReceive('listContents')
            ->once()
            ->with('cache://path', FilesystemReader::LIST_DEEP)
            ->andThrows(UnableToListContents::atLocation('cache://path', true, new Exception('test')));

        Assert::exception(
            static fn () => $persister->delete($pathInfo),
            FilesystemException::class,
            "Unable to list contents for 'cache://path'%A%",
        );
    }

    public function testExceptionShouldNotBeThrownIfFilesystemThrownExceptionOnCachedImagesListingForDeletionButExceptionAreSuppressed(): void
    {
        $filesystem = Mockery::instanceMock($this->createFilesystem([
            'path/image' => '... image content ...',
        ], [
            'path/w:100,h:200/image.png' => '... image content ...',
            'path/w:50,h:100/image.png' => '... image content ...',
        ]));
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('path');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        $filesystem->shouldReceive('listContents')
            ->once()
            ->with('cache://path', FilesystemReader::LIST_DEEP)
            ->andThrows(UnableToListContents::atLocation('cache://path', true, new Exception('test')));

        $persister->delete($pathInfo, [
            FilePersisterInterface::OPTION_SUPPRESS_EXCEPTIONS => true,
        ]);

        Assert::false($filesystem->fileExists('source://path/image'));
        Assert::true($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
        Assert::true($filesystem->fileExists('cache://path/w:50,h:100/image.png'));
    }

    public function testSourceImageAndAllCachedImagesShouldBeDeleted(): void
    {
        $filesystem = $this->createFilesystem([
            'path/image' => '... image content ...',
            'path/image2' => '... image content ...',
        ], [
            'path/w:100,h:200/image.png' => '... image content ...',
            'path/w:50,h:100/image.png' => '... image content ...',
            'path/w:50,h:100/image2.png' => '... image content ...',
        ]);
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('path');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        $persister->delete($pathInfo);

        Assert::false($filesystem->fileExists('source://path/image'));
        Assert::false($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
        Assert::false($filesystem->fileExists('cache://path/w:50,h:100/image.png'));

        Assert::true($filesystem->fileExists('source://path/image2'));
        Assert::true($filesystem->fileExists('cache://path/w:50,h:100/image2.png'));
    }

    public function testSourceImageAndAllCachedImagesWithoutNamespaceShouldBeDeleted(): void
    {
        $filesystem = $this->createFilesystem([
            'image' => '... image content ...',
            'image2' => '... image content ...',
        ], [
            'w:100,h:200/image.png' => '... image content ...',
            'w:50,h:100/image.png' => '... image content ...',
            'w:50,h:100/image2.png' => '... image content ...',
        ]);
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        $persister->delete($pathInfo);

        Assert::false($filesystem->fileExists('source://image'));
        Assert::false($filesystem->fileExists('cache://w:100,h:200/image.png'));
        Assert::false($filesystem->fileExists('cache://w:50,h:100/image.png'));

        Assert::true($filesystem->fileExists('source://image2'));
        Assert::true($filesystem->fileExists('cache://w:50,h:100/image2.png'));
    }

    public function testAllCachedImagesShouldBeDeletedButSourceImageShouldExists(): void
    {
        $filesystem = $this->createFilesystem([
            'path/image' => '... image content ...',
            'path/image2' => '... image content ...',
        ], [
            'path/w:100,h:200/image.png' => '... image content ...',
            'path/w:50,h:100/image.png' => '... image content ...',
            'path/w:50,h:100/image2.png' => '... image content ...',
        ]);
        $persister = new ImagePersister($filesystem);

        $pathInfo = Mockery::mock(ImagePathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('getPath')
            ->withNoArgs()
            ->andReturn('path/image');

        $pathInfo->shouldReceive('getNamespace')
            ->withNoArgs()
            ->andReturn('path');

        $pathInfo->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('image');

        $persister->delete($pathInfo, [
            ImagePersisterInterface::OPTION_DELETE_CACHE_ONLY => true,
        ]);

        Assert::false($filesystem->fileExists('cache://path/w:100,h:200/image.png'));
        Assert::false($filesystem->fileExists('cache://path/w:50,h:100/image.png'));

        Assert::true($filesystem->fileExists('source://path/image'));
        Assert::true($filesystem->fileExists('source://path/image2'));
        Assert::true($filesystem->fileExists('cache://path/w:50,h:100/image2.png'));
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @param array<string, string> $sourceFiles
     * @param array<string, string> $cachedFiles
     */
    private function createFilesystem(array $sourceFiles = [], array $cachedFiles = []): MountManager
    {
        $sourceFs = new Filesystem(
            new InMemoryFilesystemAdapter(),
        );

        $cacheFs = new Filesystem(
            new InMemoryFilesystemAdapter(),
        );

        foreach ($sourceFiles as $filename => $content) {
            $sourceFs->write($filename, $content);
        }

        foreach ($cachedFiles as $filename => $content) {
            $cacheFs->write($filename, $content);
        }

        return new MountManager([
            ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => $sourceFs,
            ImagePersisterInterface::FILESYSTEM_NAME_CACHE => $cacheFs,
        ]);
    }
}

(new ImagePersisterTest())->run();
