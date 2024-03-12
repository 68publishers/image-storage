<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Filesystem;

use League\Flysystem\FilesystemAdapter;
use Mockery;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class FilesystemTest extends TestCase
{
    public function testExceptionShouldBeThrownIfNameArgumentPassed(): void
    {
        $adapter = Mockery::mock(FilesystemAdapter::class);
        $filesystem = new Filesystem($adapter);

        Assert::exception(
            static fn () => $filesystem->getAdapter('test'),
            InvalidArgumentException::class,
            'The filesystem is non-prefixed.',
        );
    }

    public function testAdapterShouldBeReturned(): void
    {
        $adapter = Mockery::mock(FilesystemAdapter::class);
        $filesystem = new Filesystem($adapter);

        Assert::same($adapter, $filesystem->getAdapter());
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}

(new FilesystemTest())->run();
