<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Codec;

use Mockery;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\RuntimeCachedCodec;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class RuntimeCachedCodecTest extends TestCase
{
    public function testArrayValueShouldBeEncodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = ['w' => 100, 'h' => 200];

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $runtimeCachedCodec->modifiersToPath($value));
        Assert::same('w:100,h:200', $runtimeCachedCodec->modifiersToPath($value));
    }

    public function testStringValueShouldBeEncodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = 'preset';

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $runtimeCachedCodec->modifiersToPath($value));
        Assert::same('w:100,h:200', $runtimeCachedCodec->modifiersToPath($value));
    }

    public function testStringValueShouldBeDecodedAndCached2(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = 'w:100,h:200';

        $innerCodec->shouldReceive('pathToModifiers')
            ->once()
            ->with($value)
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->pathToModifiers($value));
        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->pathToModifiers($value));
    }

    public function testStringValueShouldBeDecodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = 'w:100,h:100';

        $innerCodec->shouldReceive('pathToModifiers')
            ->once()
            ->with($value)
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->pathToModifiers($value));
        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->pathToModifiers($value));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new RuntimeCachedCodecTest())->run();
