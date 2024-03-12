<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Codec;

use Mockery;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\RuntimeCachedCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class RuntimeCachedCodecTest extends TestCase
{
    public function testArrayValueShouldBeEncodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = new Value(['w' => 100, 'h' => 200]);

        $innerCodec->shouldReceive('encode')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $runtimeCachedCodec->encode($value));
        Assert::same('w:100,h:200', $runtimeCachedCodec->encode($value));
    }

    public function testStringValueShouldBeEncodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = new Value('preset');

        $innerCodec->shouldReceive('encode')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $runtimeCachedCodec->encode($value));
        Assert::same('w:100,h:200', $runtimeCachedCodec->encode($value));
    }

    public function testArrayValueShouldBeDecodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = new Value(['w' => 100, 'h' => 200]);

        $innerCodec->shouldReceive('decode')
            ->once()
            ->with($value)
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->decode($value));
        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->decode($value));
    }

    public function testStringValueShouldBeDecodedAndCached(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $runtimeCachedCodec = new RuntimeCachedCodec($innerCodec);
        $value = new Value('w:100,h:100');

        $innerCodec->shouldReceive('decode')
            ->once()
            ->with($value)
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->decode($value));
        Assert::same(['w' => 100, 'h' => 200], $runtimeCachedCodec->decode($value));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new RuntimeCachedCodecTest())->run();
