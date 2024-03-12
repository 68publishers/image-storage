<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Filesystem;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use Mockery;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Filesystem\AdapterProviderInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class MountManagerTest extends TestCase
{
    public function testAdaptersShouldBeReturned(): void
    {
        $filesystemA = Mockery::mock(FilesystemOperator::class, AdapterProviderInterface::class);
        $filesystemB = Mockery::mock(FilesystemOperator::class);
        $filesystemC = Mockery::mock(FilesystemOperator::class, AdapterProviderInterface::class);
        $adapterA = Mockery::mock(FilesystemAdapter::class);
        $adapterC = Mockery::mock(FilesystemAdapter::class);

        $filesystemA->shouldReceive('getAdapter')
            ->once()
            ->withNoArgs()
            ->andReturn($adapterA);

        $filesystemC->shouldReceive('getAdapter')
            ->once()
            ->withNoArgs()
            ->andReturn($adapterC);

        $mount = new MountManager([
            'a' => $filesystemA,
            'b' => $filesystemB,
            'c' => $filesystemC,
        ]);

        Assert::same($adapterA, $mount->getAdapter('a'));
        Assert::same($adapterC, $mount->getAdapter('c'));

        Assert::exception(
            static fn () => $mount->getAdapter('b'),
            InvalidArgumentException::class,
            'Adapter with prefix b:// not found.',
        );

        Assert::exception(
            static fn () => $mount->getAdapter('d'),
            InvalidArgumentException::class,
            'Adapter with prefix d:// not found.',
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}

(new MountManagerTest())->run();
