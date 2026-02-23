<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Codec;

use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\PresetCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\Preset;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class PresetCodecTest extends TestCase
{
    public function testSimpleValueShouldBeEncoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);
        $value = ['w' => 100, 'h' => 200];

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with($value)
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $presetCodec->modifiersToPath($value));
    }

    public function testPresetValueShouldBeEncoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);
        $preset = new Preset(['w' => 100, 'h' => 200], null, null);

        $config->shouldReceive('offsetGet')
            ->with(Config::MODIFIER_ASSIGNER)
            ->andReturn(':');

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        $innerCodec->shouldReceive('modifiersToPath')
            ->once()
            ->with(['w' => 100, 'h' => 200])
            ->andReturn('w:100,h:200');

        Assert::same('w:100,h:200', $presetCodec->modifiersToPath('preset'));
    }

    public function testSimpleValueShouldBeDecoded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);

        $innerCodec->shouldReceive('pathToModifiers')
            ->once()
            ->with('w:100,h:200')
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $presetCodec->pathToModifiers('w:100,h:200'));
    }

    public function testPresetValueShouldBeExpanded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);
        $preset = new Preset(['w' => 100, 'h' => 200], null, null);

        $config->shouldReceive('offsetGet')
            ->with(Config::MODIFIER_ASSIGNER)
            ->andReturn(':');

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        $innerCodec->shouldReceive('expandModifiers')
            ->once()
            ->with(['w' => 100, 'h' => 200])
            ->andReturn(['w' => 100, 'h' => 200]);

        Assert::same(['w' => 100, 'h' => 200], $presetCodec->expandModifiers('preset'));
    }

    public function testPresetValueWithDescriptorShouldBeExpanded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);
        $preset = new Preset(['ar' => '16x9'], $descriptor, 100);

        $config->shouldReceive('offsetGet')
            ->with(Config::MODIFIER_ASSIGNER)
            ->andReturn(':');

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        $descriptor->shouldReceive('validateModifierValue')
            ->once()
            ->with(true, 100)
            ->andReturn(100);

        $descriptor->shouldReceive('expandModifier')
            ->once()
            ->with($modifierCollection, 100)
            ->andReturn(['w' => 100]);

        $innerCodec->shouldReceive('expandModifiers')
            ->once()
            ->with(['ar' => '16x9', 'w' => 100])
            ->andReturn(['ar' => '16x9', 'w' => 100]);

        Assert::same(['ar' => '16x9', 'w' => 100], $presetCodec->expandModifiers('preset'));
    }

    public function testPresetValueWithDescriptorAndCustomValueShouldBeExpanded(): void
    {
        $innerCodec = Mockery::mock(CodecInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);
        $presetCodec = new PresetCodec($innerCodec, $config, $modifierCollection, $presetCollection);
        $preset = new Preset(['ar' => '16x9'], $descriptor, 100);

        $config->shouldReceive('offsetGet')
            ->with(Config::MODIFIER_ASSIGNER)
            ->andReturn(':');

        $presetCollection->shouldReceive('get')
            ->once()
            ->with('preset')
            ->andReturn($preset);

        $descriptor->shouldReceive('validateModifierValue')
            ->once()
            ->with('200', 100)
            ->andReturn(200);

        $descriptor->shouldReceive('expandModifier')
            ->once()
            ->with($modifierCollection, 200)
            ->andReturn(['w' => 200]);

        $innerCodec->shouldReceive('expandModifiers')
            ->once()
            ->with(['ar' => '16x9', 'w' => 200])
            ->andReturn(['ar' => '16x9', 'w' => 200]);

        Assert::same(['ar' => '16x9', 'w' => 200], $presetCodec->expandModifiers('preset:200'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new PresetCodecTest())->run();
