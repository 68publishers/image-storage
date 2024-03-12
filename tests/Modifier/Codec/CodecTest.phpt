<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Codec;

use Mockery;
use Mockery\MockInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Codec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../../bootstrap.php';

final class CodecTest extends TestCase
{
    public function testExceptionShouldBeThrownIfNonArrayValueIsEncoded(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        Assert::exception(
            static fn () => $codec->encode(new Value('test')),
            InvalidArgumentException::class,
            'Can not decode value of type string, the value must be array<string, string|numeric|bool>.',
        );
    }

    public function testExceptionShouldBeThrownIfEmptyArrayValueIsEncoded(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        Assert::exception(
            static fn () => $codec->encode(new Value([])),
            InvalidArgumentException::class,
            'Value can not be an empty array.',
        );
    }

    public function testValueShouldBeEncoded(): void
    {
        $config = $this->createConfigWithExpectations();
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        $modifiers = [
            'w' => Mockery::mock(ParsableModifierInterface::class),
            'pd' => Mockery::mock(ParsableModifierInterface::class),
            'ar' => Mockery::mock(ParsableModifierInterface::class),
            'flag_a' => Mockery::mock(ModifierInterface::class),
            'flag_b' => Mockery::mock(ModifierInterface::class),
        ];

        foreach ($modifiers as $alias => $modifier) {
            $modifierCollection->shouldReceive('getByAlias')
                ->once()
                ->with($alias)
                ->andReturn($modifier);
        }

        Assert::same('ar:16x9,flag_a,pd:2.5,w:100', $codec->encode(new Value([
            'w' => 100,
            'pd' => 2.5,
            'ar' => '16x9',
            'flag_a' => true,
            'flag_b' => false,
        ])));
    }

    public function testExceptionShouldBeThrownIfNonStringValueIsDecoded(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        Assert::exception(
            static fn () => $codec->decode(new Value([])),
            InvalidArgumentException::class,
            'Can not decode value of type array, the value must be string or Stringable object.',
        );
    }

    public function testExceptionShouldBeThrownIfEmptyStringValueIsDecoded(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        Assert::exception(
            static fn () => $codec->decode(new Value('')),
            InvalidArgumentException::class,
            'Value can not be an empty string.',
        );
    }

    public function testExceptionShouldBeThrownIfInvalidModifierIsDecoded(): void
    {
        $config = $this->createConfigWithExpectations();
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        Assert::exception(
            static fn () => $codec->decode(new Value('w:100:200,ar:16x9')),
            InvalidArgumentException::class,
            'An invalid path "w:100:200,ar:16x9" passed, the modifier "w:100:200" has an invalid format.',
        );
    }

    public function testExceptionShouldBeThrownIfParsableModifierWithNoValueIsDecoded(): void
    {
        $config = $this->createConfigWithExpectations();
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $modifier = Mockery::mock(ParsableModifierInterface::class);
        $codec = new Codec($config, $modifierCollection);

        $modifierCollection->shouldReceive('getByAlias')
            ->once()
            ->with('w')
            ->andReturn($modifier);

        $modifier->shouldReceive('getAlias')
            ->once()
            ->withNoArgs()
            ->andReturn('w');

        Assert::exception(
            static fn () => $codec->decode(new Value('w')),
            InvalidArgumentException::class,
            'An invalid path "w" passed, the modifier "w" must have a value.',
        );
    }

    public function testExceptionShouldBeThrownIfNonParsableModifierWithValueIsDecoded(): void
    {
        $config = $this->createConfigWithExpectations();
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $modifier = Mockery::mock(ModifierInterface::class);
        $codec = new Codec($config, $modifierCollection);

        $modifierCollection->shouldReceive('getByAlias')
            ->once()
            ->with('flag_a')
            ->andReturn($modifier);

        $modifier->shouldReceive('getAlias')
            ->once()
            ->withNoArgs()
            ->andReturn('flag_a');

        Assert::exception(
            static fn () => $codec->decode(new Value('flag_a:value')),
            InvalidArgumentException::class,
            'An invalid path "flag_a:value" passed, the modifier "flag_a" can not have a value.',
        );
    }

    public function testValueShouldBeDecoded(): void
    {
        $config = $this->createConfigWithExpectations();
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $codec = new Codec($config, $modifierCollection);

        $modifiers = [
            'w' => Mockery::mock(ParsableModifierInterface::class),
            'pd' => Mockery::mock(ParsableModifierInterface::class),
            'ar' => Mockery::mock(ParsableModifierInterface::class),
            'flag_a' => Mockery::mock(ModifierInterface::class),
        ];

        foreach ($modifiers as $alias => $modifier) {
            assert($modifier instanceof MockInterface);

            $modifierCollection->shouldReceive('getByAlias')
                ->once()
                ->with($alias)
                ->andReturn($modifier);

            $modifier->shouldReceive('getAlias')
                ->once()
                ->withNoArgs()
                ->andReturn($alias);
        }

        Assert::same([
            'ar' => '16x9',
            'flag_a' => true,
            'pd' => '2.5',
            'w' => '100',
        ], $codec->decode(new Value('ar:16x9,flag_a,pd:2.5,w:100')));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createConfigWithExpectations(): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::MODIFIER_ASSIGNER)
            ->andReturn(':');

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::MODIFIER_SEPARATOR)
            ->andReturn(',');

        return $config;
    }
}

(new CodecTest())->run();
