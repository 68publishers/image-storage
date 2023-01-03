<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\DI;

use Closure;
use Tester\Assert;
use Tester\TestCase;
use Nette\DI\Container;
use ReflectionProperty;
use League\Flysystem\Visibility;
use Tester\CodeCoverage\Collector;
use Intervention\Image\ImageManager;
use League\Flysystem\Config as FlysystemConfig;
use SixtyEightPublishers\ImageStorage\Modifier;
use SixtyEightPublishers\ImageStorage\ImageStorage;
use SixtyEightPublishers\ImageStorage\Config\Config;
use League\Flysystem\Filesystem as FlysystemFilesystem;
use SixtyEightPublishers\ImageStorage\Info\InfoFactory;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use SixtyEightPublishers\FileStorage\FileStorageProvider;
use SixtyEightPublishers\ImageStorage\Modifier\Validator;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfig;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolver;
use SixtyEightPublishers\ImageStorage\Resource\ResourceFactory;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategy;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGenerator;
use SixtyEightPublishers\ImageStorage\Tests\Fixtures\TestModifier;
use SixtyEightPublishers\ImageStorage\Tests\Fixtures\TestValidator;
use SixtyEightPublishers\ImageStorage\Tests\Fixtures\TestApplicator;
use SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollection;
use SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServerFactory;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ExternalImageServerFactory;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;
use SixtyEightPublishers\FileStorage\Bridge\Symfony\Console\Configurator\BaseCleanCommandConfigurator;
use SixtyEightPublishers\FileStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfiguratorRegistry;
use SixtyEightPublishers\FileStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfiguratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick\Driver as SixtyEightPublishersImagickDriver;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfigurator as ImageStorageCleanCommandConfigurator;
use function count;
use function assert;
use function array_map;
use function get_class;
use function is_string;
use function array_values;
use function call_user_func;
use function iterator_to_array;

require __DIR__ . '/../../../bootstrap.php';

final class ImageStorageExtensionTest extends TestCase
{
	public function testExceptionShouldBeThrownIfFileStorageExtensionIsNotRegistered(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.error.missingFileStorageExtension.neon'),
			RuntimeException::class,
			'The extension SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension can be used only with SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension.'
		);
	}

	public function testExceptionShouldBeThrownIfImageStorageIsNotDefinedInFileStorage(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.error.missingDefinitionInFileStorageExtension.neon'),
			RuntimeException::class,
			'Missing definition for a storage with the name "missing_in_file_storage" in the configuration of the extension SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension.'
		);
	}

	public function testExtensionShouldBeIntegratedWithMinimalConfiguration(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.minimal.neon');

		$this->assertImageManager($container, ImageStorageExtension::DRIVER_GD);
		$this->assertStorageCleaner($container);
		$this->assertImageStorage(
			container: $container,
			imageServerFactoryType: LocalImageServerFactory::class,
			signatureStrategyType: null,
			configOptions: [
				ConfigInterface::BASE_PATH => 'images',
				ConfigInterface::HOST => null,
				Config::MODIFIER_SEPARATOR => ',',
				Config::MODIFIER_ASSIGNER => ':',
				ConfigInterface::VERSION_PARAMETER_NAME => '_v',
				Config::SIGNATURE_PARAMETER_NAME => '_s',
				Config::SIGNATURE_KEY => null,
				Config::SIGNATURE_ALGORITHM => 'sha256',
				Config::ALLOWED_PIXEL_DENSITY => [],
				Config::ALLOWED_RESOLUTIONS => [],
				Config::ALLOWED_QUALITIES => [],
				Config::ENCODE_QUALITY => 90,
				Config::CACHE_MAX_AGE => 31536000,
			]
		);
		$this->assertModifierFacade(
			container: $container,
			modifierTypes: [
				Modifier\Original::class,
				Modifier\Height::class,
				Modifier\Width::class,
				Modifier\AspectRatio::class,
				Modifier\Fit::class,
				Modifier\PixelDensity::class,
				Modifier\Orientation::class,
				Modifier\Quality::class,
			],
			applicatorTypes: [
				Applicator\Orientation::class,
				Applicator\Resize::class,
				Applicator\Format::class,
			],
			validatorTypes: [
				Validator\AllowedResolutionValidator::class,
				Validator\AllowedPixelDensityValidator::class,
				Validator\AllowedQualityValidator::class,
			],
			presets: []
		);
		$this->assertNoImageConfig(
			container: $container,
			defaultPath: null,
			paths: [],
			patterns: []
		);
	}

	public function testExtensionShouldBeIntegratedWithImagickDriver(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withImagickDriver.neon');

		$this->assertImageManager($container, ImageStorageExtension::DRIVER_IMAGICK);
	}

	public function testExtensionShouldBeIntegratedWith68publishersImagickDriver(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.with68publishersImagickDriver.neon');

		$this->assertImageManager($container, SixtyEightPublishersImagickDriver::class);
	}

	public function testExtensionShouldBeIntegratedWithExternalImageServer(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withExternalImageServer.neon');

		$this->assertImageStorage(
			container: $container,
			imageServerFactoryType: ExternalImageServerFactory::class,
			signatureStrategyType: null,
			configOptions: [
				ConfigInterface::BASE_PATH => '',
				ConfigInterface::HOST => 'https://www.example.com',
				Config::MODIFIER_SEPARATOR => ',',
				Config::MODIFIER_ASSIGNER => ':',
				ConfigInterface::VERSION_PARAMETER_NAME => '_v',
				Config::SIGNATURE_PARAMETER_NAME => '_s',
				Config::SIGNATURE_KEY => null,
				Config::SIGNATURE_ALGORITHM => 'sha256',
				Config::ALLOWED_PIXEL_DENSITY => [],
				Config::ALLOWED_RESOLUTIONS => [],
				Config::ALLOWED_QUALITIES => [],
				Config::ENCODE_QUALITY => 90,
				Config::CACHE_MAX_AGE => 31536000,
			]
		);
	}

	public function testExtensionShouldBeIntegratedWithSignatureStrategy(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withSignatureStrategy.neon');

		$this->assertImageStorage(
			container: $container,
			imageServerFactoryType: LocalImageServerFactory::class,
			signatureStrategyType: SignatureStrategy::class,
			configOptions: [
				ConfigInterface::BASE_PATH => 'images',
				ConfigInterface::HOST => null,
				Config::MODIFIER_SEPARATOR => ',',
				Config::MODIFIER_ASSIGNER => ':',
				ConfigInterface::VERSION_PARAMETER_NAME => '_v',
				Config::SIGNATURE_PARAMETER_NAME => '_s',
				Config::SIGNATURE_KEY => 'abc',
				Config::SIGNATURE_ALGORITHM => 'sha256',
				Config::ALLOWED_PIXEL_DENSITY => [],
				Config::ALLOWED_RESOLUTIONS => [],
				Config::ALLOWED_QUALITIES => [],
				Config::ENCODE_QUALITY => 90,
				Config::CACHE_MAX_AGE => 31536000,
			]
		);
	}

	public function testExtensionShouldBeIntegratedWithCustomModifiersAndApplicatorsAndValidatorsAndPresets(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withModifiersAndApplicatorsAndValidatorsAndPresets.neon');

		$this->assertModifierFacade(
			container: $container,
			modifierTypes: [
				Modifier\Original::class,
				Modifier\Height::class,
				Modifier\Width::class,
				Modifier\AspectRatio::class,
				Modifier\Fit::class,
				Modifier\PixelDensity::class,
				Modifier\Orientation::class,
				Modifier\Quality::class,
				TestModifier::class,
			],
			applicatorTypes: [
				TestApplicator::class,
				Applicator\Orientation::class,
				Applicator\Resize::class,
				Applicator\Format::class,
			],
			validatorTypes: [
				TestValidator::class,
			],
			presets: [
				'small' => [
					'w' => 100,
					'ar' => '2x1',
				],
				'huge' => [
					'w' => 1000,
					'ar' => '16x9',
				],
				'rotated' => [
					'o' => 180,
				],
			]
		);
	}

	public function testExtensionShouldBeIntegratedWithNoImageOptions(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withNoImageOptions.neon');

		$this->assertNoImageConfig(
			container: $container,
			defaultPath: 'nomiage/noimage.png',
			paths: [
				'test' => 'test/noimage.png',
			],
			patterns: [
				'test' => '^test\/',
			]
		);
	}

	public function testCleanCommandConfiguratorShouldBeRegisteredIfConsoleExtensionIsRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/config/ImageStorage/config.withConsoleExtension.neon');

		$this->assertCleanCommandConfigurator($container, [
			BaseCleanCommandConfigurator::class,
			ImageStorageCleanCommandConfigurator::class,
		]);
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}

	private function assertImageManager(Container $container, string $driver): void
	{
		$imageManager = $container->getByType(ImageManager::class);
		assert($imageManager instanceof ImageManager);

		Assert::hasKey('driver', $imageManager->config);

		$managerDriver = $imageManager->config['driver'];

		if (is_string($managerDriver)) {
			Assert::same($driver, $managerDriver);
		} else {
			Assert::type($driver, $managerDriver);
		}
	}

	private function assertStorageCleaner(Container $container): void
	{
		$cleaner = $container->getByType(StorageCleanerInterface::class);

		Assert::type(StorageCleaner::class, $cleaner);
	}

	private function assertCleanCommandConfigurator(Container $container, array $configuratorTypes): void
	{
		$configurator = $container->getByType(CleanCommandConfiguratorInterface::class);

		Assert::type(CleanCommandConfiguratorRegistry::class, $configurator);
		assert($configurator instanceof CleanCommandConfiguratorRegistry);

		call_user_func(Closure::bind(
			static function () use ($configurator, $configuratorTypes): void {
				Assert::same(count($configuratorTypes), count($configurator->configurators));

				foreach ($configuratorTypes as $index => $configuratorType) {
					Assert::type($configuratorType, $configurator->configurators[$index]);
				}
			},
			null,
			CleanCommandConfiguratorRegistry::class
		));
	}

	private function assertImageStorage(
		Container $container,
		string $imageServerFactoryType,
		?string $signatureStrategyType,
		array $configOptions,
	): void {
		$provider = $container->getByType(FileStorageProviderInterface::class);
		assert($provider instanceof FileStorageProvider);

		$imageStorage = $provider->get('images');
		$mountManager = $imageStorage->getFilesystem();

		Assert::type(ImageStorage::class, $imageStorage);
		Assert::type(MountManager::class, $mountManager);

		assert($mountManager instanceof MountManager);

		$this->assertMountManager($mountManager);

		call_user_func(Closure::bind(
			static function () use ($imageStorage, $configOptions, $imageServerFactoryType, $signatureStrategyType): void {
				assert($imageStorage instanceof ImageStorage);

				Assert::type(ResourceFactory::class, $imageStorage->resourceFactory);
				Assert::type(LinkGenerator::class, $imageStorage->linkGenerator);
				Assert::type(NoImageResolver::class, $imageStorage->noImageResolver);
				Assert::type(InfoFactory::class, $imageStorage->infoFactory);
				Assert::type($imageServerFactoryType, $imageStorage->imageServerFactory);

				if (null === $signatureStrategyType) {
					Assert::null($imageStorage->linkGenerator->getSignatureStrategy());
				} else {
					Assert::type($signatureStrategyType, $imageStorage->linkGenerator->getSignatureStrategy());
				}

				$config = $imageStorage->getConfig();

				call_user_func(Closure::bind(
					static function () use ($config, $configOptions): void {
						Assert::same($configOptions, $config->config);
					},
					null,
					Config::class
				));
			},
			null,
			ImageStorage::class
		));
	}

	private function assertMountManager(MountManager $filesystem): void
	{
		$assertInMemoryFilesystem = function (Filesystem $filesystem, array $configOptions): void {
			$this->assertInMemoryFilesystem($filesystem, $configOptions);
		};

		call_user_func(Closure::bind(
			static function () use ($filesystem, $assertInMemoryFilesystem): void {
				$filesystems = $filesystem->filesystems;

				Assert::hasKey(ImagePersisterInterface::FILESYSTEM_NAME_SOURCE, $filesystems);
				Assert::hasKey(ImagePersisterInterface::FILESYSTEM_NAME_CACHE, $filesystems);

				$sourceFs = $filesystems[ImagePersisterInterface::FILESYSTEM_NAME_SOURCE];
				$cacheFs = $filesystems[ImagePersisterInterface::FILESYSTEM_NAME_CACHE];

				Assert::type(Filesystem::class, $sourceFs);
				Assert::type(Filesystem::class, $cacheFs);

				$assertInMemoryFilesystem($sourceFs, [
					FlysystemConfig::OPTION_VISIBILITY => Visibility::PRIVATE,
					FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PRIVATE,
				]);
				$assertInMemoryFilesystem($cacheFs, [
					FlysystemConfig::OPTION_VISIBILITY => Visibility::PUBLIC,
					FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
				]);
			},
			null,
			MountManager::class
		));
	}

	private function assertInMemoryFilesystem(Filesystem $filesystem, array $configOptions): void
	{
		Assert::type(InMemoryFilesystemAdapter::class, $filesystem->getAdapter());

		$configProperty = new ReflectionProperty(FlysystemFilesystem::class, 'config');
		$config = $configProperty->getValue($filesystem);
		assert($config instanceof FlysystemConfig);

		foreach ($configOptions as $opt => $value) {
			Assert::same($value, $config->get($opt));
		}
	}

	private function assertModifierFacade(
		Container $container,
		array $modifierTypes,
		array $applicatorTypes,
		array $validatorTypes,
		array $presets,
	): void {
		$modifierFacade = $container->getService('image_storage.modifier_facade.images');
		assert($modifierFacade instanceof ModifierFacade);

		call_user_func(Closure::bind(
			static function () use ($modifierFacade, $applicatorTypes, $validatorTypes, $presets): void {
				Assert::same($applicatorTypes, array_map(
					static fn (ModifierApplicatorInterface $applicator): string => get_class($applicator),
					$modifierFacade->applicators
				));

				Assert::same($validatorTypes, array_map(
					static fn (ValidatorInterface $validator): string => get_class($validator),
					$modifierFacade->validators
				));

				$presetCollection = $modifierFacade->presetCollection;
				Assert::type(PresetCollection::class, $presetCollection);

				call_user_func(Closure::bind(
					static function () use ($presetCollection, $presets): void {
						Assert::same($presets, $presetCollection->presets);
					},
					null,
					PresetCollection::class
				));
			},
			null,
			ModifierFacade::class
		));

		$modifiers = array_values(iterator_to_array($modifierFacade->getModifierCollection()));

		Assert::same($modifierTypes, array_map(
			static fn (ModifierInterface $modifier): string => get_class($modifier),
			$modifiers
		));
	}

	private function assertNoImageConfig(Container $container, ?string $defaultPath, array $paths, array $patterns): void
	{
		$noImageConfig = $container->getService('image_storage.no_image_config.images');
		assert($noImageConfig instanceof NoImageConfig);

		Assert::same($defaultPath, $noImageConfig->getDefaultPath());
		Assert::same($paths, $noImageConfig->getPaths());
		Assert::same($patterns, $noImageConfig->getPatterns());
	}
}

(new ImageStorageExtensionTest())->run();
