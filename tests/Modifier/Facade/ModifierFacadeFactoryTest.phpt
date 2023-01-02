<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Facade;

use Closure;
use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Codec;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\PresetCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\RuntimeCachedCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeFactory;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface;
use function assert;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class ModifierFacadeFactoryTest extends TestCase
{
	public function testModifierFacadeShouldBeCreated(): void
	{
		$presetCollectionFactory = Mockery::mock(PresetCollectionFactoryInterface::class);
		$presetCollection = Mockery::mock(PresetCollectionInterface::class);
		$modifierCollectionFactory = Mockery::mock(ModifierCollectionFactoryInterface::class);
		$modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
		$config = Mockery::mock(ConfigInterface::class);

		$presetCollectionFactory->shouldReceive('create')
			->once()
			->withNoArgs()
			->andReturn($presetCollection);

		$modifierCollectionFactory->shouldReceive('create')
			->once()
			->withNoArgs()
			->andReturn($modifierCollection);

		$modifierFacadeFactory = new ModifierFacadeFactory($presetCollectionFactory, $modifierCollectionFactory);
		$modifierFacade = $modifierFacadeFactory->create($config);

		$this->assertModifierFacade($modifierFacade, $presetCollection, $modifierCollection, $config);
	}

	public function assertModifierFacade(ModifierFacadeInterface $modifierFacade, PresetCollectionInterface $presetCollection, ModifierCollectionInterface $modifierCollection, ConfigInterface $config): void
	{
		Assert::type(ModifierFacade::class, $modifierFacade);
		assert($modifierFacade instanceof ModifierFacade);

		$assertRuntimeCachedCodec = [$this, 'assertRuntimeCachedCodec'];

		call_user_func(Closure::bind(
			static function () use ($modifierFacade, $presetCollection, $modifierCollection, $config, $assertRuntimeCachedCodec): void {
				Assert::same($config, $modifierFacade->config);
				Assert::same($presetCollection, $modifierFacade->presetCollection);
				Assert::same($modifierCollection, $modifierFacade->modifierCollection);

				$runtimeCachedCodec = $modifierFacade->getCodec();
				Assert::type(RuntimeCachedCodec::class, $runtimeCachedCodec);

				$assertRuntimeCachedCodec($runtimeCachedCodec, $presetCollection, $modifierCollection, $config);
			},
			null,
			ModifierFacade::class
		));
	}

	public function assertRuntimeCachedCodec(RuntimeCachedCodec $codec, PresetCollectionInterface $presetCollection, ModifierCollectionInterface $modifierCollection, ConfigInterface $config): void
	{
		$assertPresetCodec = [$this, 'assertPresetCodec'];

		call_user_func(Closure::bind(
			static function () use ($codec, $presetCollection, $modifierCollection, $config, $assertPresetCodec): void {
				$presetCodec = $codec->codec;

				Assert::type(PresetCodec::class, $presetCodec);

				$assertPresetCodec($presetCodec, $presetCollection, $modifierCollection, $config);
			},
			null,
			RuntimeCachedCodec::class
		));
	}

	public function assertPresetCodec(PresetCodec $codec, PresetCollectionInterface $presetCollection, ModifierCollectionInterface $modifierCollection, ConfigInterface $config): void
	{
		$assertBaseCodec = [$this, 'assertBaseCodec'];

		call_user_func(Closure::bind(
			static function () use ($codec, $presetCollection, $modifierCollection, $config, $assertBaseCodec): void {
				$baseCodec = $codec->codec;

				Assert::type(Codec::class, $baseCodec);
				Assert::same($presetCollection, $codec->presetCollection);

				$assertBaseCodec($baseCodec, $modifierCollection, $config);
			},
			null,
			PresetCodec::class
		));
	}

	public function assertBaseCodec(Codec $codec, ModifierCollectionInterface $modifierCollection, ConfigInterface $config): void
	{
		call_user_func(Closure::bind(
			static function () use ($codec, $modifierCollection, $config): void {
				Assert::same($config, $codec->config);
				Assert::same($modifierCollection, $codec->modifierCollection);
			},
			null,
			Codec::class
		));
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}
}

(new ModifierFacadeFactoryTest())->run();
