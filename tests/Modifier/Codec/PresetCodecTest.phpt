<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Codec;

use Mockery;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\PresetCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class PresetCodecTest extends TestCase
{
    public function testSimpleValueShouldBeEncoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $presetCollection);
        $value = new Value(['w' => 100, 'h' => 200]);

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $presetCodec->modifiersToPath($value));
    }

    public function testPresetValueShouldBeEncoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $presetCollection);
        $value = new PresetValue('preset');
        $preset = ['w' => 100, 'h' => 200];

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with(Mockery::type(Value::class))
            ->andReturnUsing(static function (Value $value) use ($preset): string {
                Assert::same($preset, $value->getValue());

                return 'w:100,h:200';
            });

        Assert::same('w:100,h:200', $presetCodec->modifiersToPath($value));
    }

    public function testSimpleValueShouldBeDecoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $presetCollection);
        $value = new Value('w:100,h:200');

        $innerCodec->shouldReceive('pathToModifiers')
            ->once()
            ->with($value)
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $presetCodec->pathToModifiers($value));
    }

    public function testPresetValueShouldBeDecoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $presetCollection);
        $value = new PresetValue('preset');
        $preset = ['w' => 100, 'h' => 200];

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        Assert::same(['w' => 100, 'h' => 200], $presetCodec->pathToModifiers($value));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new PresetCodecTest())->run();
