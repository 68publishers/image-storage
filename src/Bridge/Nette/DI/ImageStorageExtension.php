<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Routing\Router;
use Nette\DI\CompilerExtension;
use League\Flysystem\Visibility;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;
use Intervention\Image\ImageManager;
use League\Flysystem\FilesystemOperator;
use Nette\Application\IPresenterFactory;
use Nette\DI\Definitions\ServiceDefinition;
use League\Flysystem\Config as FlysystemConfig;
use SixtyEightPublishers\ImageStorage\Modifier;
use SixtyEightPublishers\ImageStorage\ImageStorage;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Info\InfoFactory;
use SixtyEightPublishers\ImageStorage\Modifier\Validator;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfig;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolver;
use SixtyEightPublishers\ImageStorage\Resource\ResourceFactory;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersister;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategy;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGenerator;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\FileStorage\Cleaner\StorageCleanerInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollection;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageExtension;
use SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServerFactory;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config\StorageConfig;
use SixtyEightPublishers\ImageStorage\ImageServer\ResponseFactoryInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeFactory;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\Config\FilesystemConfig;
use SixtyEightPublishers\ImageStorage\ImageServer\ExternalImageServerFactory;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ResponseFactory;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\Application\ImageServerRoute;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config\ImageStorageConfig;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageConsoleExtension;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\Application\ImageServerPresenter;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageDefinitionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\Config\StorageConfig as FileStorageConfig;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager\ImageManagerFactory;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfigurator;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager\ImageManagerFactoryInterface;
use SixtyEightPublishers\FileStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfiguratorInterface;
use function assert;
use function sprintf;
use function is_array;
use function array_diff;
use function array_keys;

final class ImageStorageExtension extends CompilerExtension implements FileStorageDefinitionFactoryInterface
{
	public const DRIVER_GD = 'gd';
	public const DRIVER_IMAGICK = 'imagick';
	public const DRIVER_68PUBLISHERS_IMAGICK = '68publishers.imagick';

	public const IMAGE_SERVER_LOCAL = 'local';
	public const IMAGE_SERVER_EXTERNAL = 'external';

	/** @var array<string> */
	private array $managed = [];

	/** @var array<string, mixed> */
	private array $routes = [];

	private bool $imageServerPresenterRegistered = false;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'driver' => Expect::anyOf(Statement::class, self::DRIVER_GD, self::DRIVER_IMAGICK, self::DRIVER_68PUBLISHERS_IMAGICK)
				->default(self::DRIVER_GD)
				->dynamic(),
			'storages' => Expect::arrayOf(
				Expect::structure([
					'source_filesystem' => Expect::structure([
						'adapter' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
							->required()
							->before(static function ($factory) {
								return $factory instanceof Statement ? $factory : new Statement($factory);
							}),
						'config' => Expect::array([
							FlysystemConfig::OPTION_VISIBILITY => Visibility::PRIVATE,
							FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PRIVATE,
						])->mergeDefaults(),
					])->castTo(FilesystemConfig::class),

					'server' => Expect::anyOf(self::IMAGE_SERVER_LOCAL, self::IMAGE_SERVER_EXTERNAL)
						->default(self::IMAGE_SERVER_LOCAL),
					'route' => Expect::bool(false),

					'no_image' => Expect::arrayOf('string', 'string')
						->default([]),
					'no_image_patterns' => Expect::arrayOf('string', 'string')
						->default([]),
					'presets' => Expect::arrayOf(
						Expect::arrayOf(Expect::scalar(), 'string'),
						'string'
					)->default([]),

					'modifiers' => Expect::listOf('string|' . Statement::class),
					'applicators' => Expect::listOf('string|' . Statement::class),
					'validators' => Expect::listOf('string|' . Statement::class),

				])->before(function (array $config): array {
					$config['modifiers'] = $this->normalizeListOfStatementsWithDefaults(
						is_array($config['modifiers'] ?? null) ? $config['modifiers'] : ['@default'],
						[
							new Statement(Modifier\Original::class),
							new Statement(Modifier\Height::class),
							new Statement(Modifier\Width::class),
							new Statement(Modifier\AspectRatio::class),
							new Statement(Modifier\Fit::class),
							new Statement(Modifier\PixelDensity::class),
							new Statement(Modifier\Orientation::class),
							new Statement(Modifier\Quality::class),
						]
					);

					$config['applicators'] = $this->normalizeListOfStatementsWithDefaults(
						is_array($config['applicators'] ?? null) ? $config['applicators'] : ['@default'],
						[
							new Statement(Applicator\Orientation::class),
							new Statement(Applicator\Resize::class),
							new Statement(Applicator\Format::class), # must be last
						]
					);

					$config['validators'] = $this->normalizeListOfStatementsWithDefaults(
						is_array($config['validators'] ?? null) ? $config['validators'] : ['@default'],
						[
							new Statement(Validator\AllowedResolutionValidator::class),
							new Statement(Validator\AllowedPixelDensityValidator::class),
							new Statement(Validator\AllowedQualityValidator::class),
						]
					);

					return $config;
				})->castTo(StorageConfig::class)
			),
		])->castTo(ImageStorageConfig::class);
	}

	public function loadConfiguration(): void
	{
		if (0 >= count($this->compiler->getExtensions(FileStorageExtension::class))) {
			throw new RuntimeException(sprintf(
				'The extension %s can be used only with %s.',
				self::class,
				FileStorageExtension::class
			));
		}

		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		assert($config instanceof ImageStorageConfig);

		# Image manager
		$builder->addDefinition($this->prefix('image_manager_factory'))
			->setAutowired(false)
			->setType(ImageManagerFactoryInterface::class)
			->setFactory(ImageManagerFactory::class);

		$builder->addDefinition($this->prefix('image_manager'))
			->setType(ImageManager::class)
			->setFactory([$this->prefix('@image_manager_factory'), 'create'], [
				['driver' => $config->driver],
			]);

		# Modifier collection factory
		$builder->addFactoryDefinition($this->prefix('modifiers.modifier_collection_factory'))
			->setAutowired(false)
			->setImplement(ModifierCollectionFactoryInterface::class)
			->getResultDefinition()
			->setFactory(ModifierCollection::class);

		# Preset collection factory
		$builder->addFactoryDefinition($this->prefix('modifiers.preset_collection_factory'))
			->setAutowired(false)
			->setImplement(PresetCollectionFactoryInterface::class)
			->getResultDefinition()
			->setFactory(PresetCollection::class);

		# Modifier facade factory
		$builder->addDefinition($this->prefix('modifiers.modifier_facade_factory'))
			->setAutowired(false)
			->setType(ModifierFacadeFactoryInterface::class)
			->setFactory(ModifierFacadeFactory::class, [
				new Reference($this->prefix('modifiers.preset_collection_factory')),
				new Reference($this->prefix('modifiers.modifier_collection_factory')),
			]);

		# Responsive - srcset generator factory
		$builder->addFactoryDefinition($this->prefix('responsive.srcset_generator_factory'))
			->setAutowired(false)
			->setImplement(SrcSetGeneratorFactoryInterface::class);

		# Image server - response factory
		$builder->addDefinition($this->prefix('image_server_response_factory'))
			->setAutowired(false)
			->setType(ResponseFactoryInterface::class)
			->setFactory(ResponseFactory::class);

		# Custom storage cleaner
		$builder->addDefinition($this->prefix('storage_cleaner'))
			->setAutowired(false)
			->setType(StorageCleanerInterface::class)
			->setFactory(StorageCleaner::class);

		# Console - extends clean command configurator if the FileStorageConsoleExtension is registered
		if (0 < \count($this->compiler->getExtensions(FileStorageConsoleExtension::class))) {
			$builder->addDefinition($this->prefix('configurator.clean_command'))
				->setType(CleanCommandConfiguratorInterface::class)
				->setFactory(CleanCommandConfigurator::class)
				->addTag(FileStorageConsoleExtension::TAG_CLEAN_COMMAND_CONFIGURATOR)
				->setAutowired(false);
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		assert($config instanceof ImageStorageConfig);

		$diff = array_diff(array_keys($config->storages), $this->managed);

		if (0 < \count($diff)) {
			throw new RuntimeException(sprintf(
				'Missing definition for a storage with the name "%s" in the configuration of the extension %s.',
				array_shift($diff),
				FileStorageExtension::class
			));
		}

		$storageCleanerDecorator = $builder->getDefinition($this->prefix('storage_cleaner'));
		$defaultStorageCleaner = $builder->getDefinitionByType(StorageCleanerInterface::class);
		assert($storageCleanerDecorator instanceof ServiceDefinition && $defaultStorageCleaner instanceof ServiceDefinition);

		$storageCleanerDecorator->setArguments([$defaultStorageCleaner]);
		$storageCleanerDecorator->setAutowired();
		$defaultStorageCleaner->setAutowired(false);

		if (empty($this->routes)) {
			return;
		}

		$presenterFactory = $builder->getDefinitionByType(IPresenterFactory::class);
		$router = $builder->getDefinitionByType(Router::class);
		assert($presenterFactory instanceof ServiceDefinition && $router instanceof ServiceDefinition);

		$presenterFactory->addSetup('setMapping', [
			[
				'ImageStorage' => ['SixtyEightPublishers\\ImageStorage\\Bridge\\Nette\\Application', '*', '*Presenter'],
			],
		]);

		foreach ($this->routes as $storageName => $basePath) {
			$router->addSetup('prepend', [
				'router' => new Statement(ImageServerRoute::class, [
					$storageName,
					$basePath,
				]),
			]);
		}
	}

	public function canCreateFileStorage(string $name, FileStorageConfig $config): bool
	{
		$extensionConfig = $this->getConfig();
		assert($extensionConfig instanceof ImageStorageConfig);

		return isset($extensionConfig->storages[$name]);
	}

	public function createFileStorage(string $name, FileStorageConfig $config): ServiceDefinition
	{
		$builder = $this->getContainerBuilder();
		$extensionConfig = $this->getConfig();
		assert($extensionConfig instanceof ImageStorageConfig);

		$this->managed[] = $name;
		$imageStorageConfig = $extensionConfig->storages[$name];

		$builder->addDefinition($this->prefix('filesystem.' . $name))
			->setType(FilesystemOperator::class)
			->setFactory(MountManager::class, [
				[
					ImagePersisterInterface::FILESYSTEM_NAME_CACHE => new Statement(Filesystem::class, [
						$config->filesystem->adapter,
						$config->filesystem->config,
					]),
					ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => new Statement(Filesystem::class, [
						$imageStorageConfig->source_filesystem->adapter,
						$imageStorageConfig->source_filesystem->config,
					]),
				],
			])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('config.' . $name))
			->setType(ConfigInterface::class)
			->setFactory(Config::class, [$config->config])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('modifier_facade.' . $name))
			->setType(ModifierFacadeInterface::class)
			->setFactory(new Statement([$this->prefix('@modifiers.modifier_facade_factory'), 'create'], [
				new Reference($this->prefix('config.' . $name)),
			]))
			->addSetup('setModifiers', [$imageStorageConfig->modifiers])
			->addSetup('setPresets', [$imageStorageConfig->presets])
			->addSetup('setApplicators', [$imageStorageConfig->applicators])
			->addSetup('setValidators', [$imageStorageConfig->validators])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('resource_factory.' . $name))
			->setType(ResourceFactoryInterface::class)
			->setFactory(ResourceFactory::class, [
				new Reference($this->prefix('filesystem.' . $name)),
				new Reference($this->prefix('image_manager')),
				new Reference($this->prefix('modifier_facade.' . $name)),
			])
			->setAutowired(false);

		# signature enabled
		if (!empty($config->config[Config::SIGNATURE_KEY] ?? '')) {
			$signatureStrategyDefinition = $builder->addDefinition($this->prefix('signature_strategy.' . $name))
				->setType(SignatureStrategyInterface::class)
				->setFactory(SignatureStrategy::class, [
					new Reference($this->prefix('config.' . $name)),
				])
				->setAutowired(false);
		}

		$builder->addDefinition($this->prefix('link_generator.' . $name))
			->setType(LinkGeneratorInterface::class)
			->setFactory(LinkGenerator::class, [
				new Reference($this->prefix('config.' . $name)),
				new Reference($this->prefix('modifier_facade.' . $name)),
				new Reference($this->prefix('responsive.srcset_generator_factory')),
				$signatureStrategyDefinition ?? null,
			])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('image_persister.' . $name))
			->setType(ImagePersisterInterface::class)
			->setFactory(ImagePersister::class, [
				new Reference($this->prefix('filesystem.' . $name)),
				new Reference($this->prefix('config.' . $name)),
				new Reference($this->prefix('modifier_facade.' . $name)),
			])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('info_factory.' . $name))
			->setType(InfoFactoryInterface::class)
			->setFactory(InfoFactory::class, [
				new Reference($this->prefix('modifier_facade.' . $name)),
				new Reference($this->prefix('link_generator.' . $name)),
				$name,
			])
			->setAutowired(false);

		$noImages = $imageStorageConfig->no_image;
		$defaultNoImage = $noImages['default'] ?? null;

		if (null !== $defaultNoImage) {
			unset($noImages['default']);
		}

		$builder->addDefinition($this->prefix('no_image_config.' . $name))
			->setType(NoImageConfigInterface::class)
			->setFactory(NoImageConfig::class, [
				$defaultNoImage,
				$noImages,
				$imageStorageConfig->no_image_patterns,
			])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('no_image_resolver.' . $name))
			->setType(NoImageResolverInterface::class)
			->setFactory(NoImageResolver::class, [
				new Reference($this->prefix('info_factory.' . $name)),
				new Reference($this->prefix('no_image_config.' . $name)),
			])
			->setAutowired(false);

		$imageServerDefinition = $builder->addDefinition($this->prefix('image_server_factory.' . $name))
			->setType(ImageServerFactoryInterface::class)
			->setAutowired(false);

		switch ($imageStorageConfig->server) {
			case self::IMAGE_SERVER_LOCAL:
				if ($imageStorageConfig->route && '' === ($config->config[ConfigInterface::BASE_PATH] ?? '')) {
					throw new RuntimeException(sprintf(
						'Unable to register a route for an image storage with the name "%s". Please set a configuration option "%s".',
						$name,
						ConfigInterface::BASE_PATH
					));
				}

				if ($imageStorageConfig->route) {
					$this->registerImageServerPresenter();
					$this->routes[$name] = $config->config[ConfigInterface::BASE_PATH];
				}

				$imageServerDefinition->setFactory(LocalImageServerFactory::class, [
					new Reference($this->prefix('image_server_response_factory')),
				]);

				break;
			case self::IMAGE_SERVER_EXTERNAL:
				if ($imageStorageConfig->route) {
					throw new RuntimeException(sprintf(
						'Unable to register a route for an image storage with the name "%s" because a server is set as external.',
						$name
					));
				}

				$imageServerDefinition->setFactory(ExternalImageServerFactory::class);

				break;
		}

		return $builder->addDefinition($this->prefix('image_storage.' . $name))
			->setType(ImageStorageInterface::class)
			->setFactory(ImageStorage::class, [
				$name,
				new Reference($this->prefix('config.' . $name)),
				new Reference($this->prefix('resource_factory.' . $name)),
				new Reference($this->prefix('link_generator.' . $name)),
				new Reference($this->prefix('image_persister.' . $name)),
				new Reference($this->prefix('no_image_resolver.' . $name)),
				new Reference($this->prefix('info_factory.' . $name)),
				$imageServerDefinition,
			])
			->setAutowired(false);
	}

	/**
	 * @param array<string|Statement> $items
	 * @param array<Statement>        $defaults
	 *
	 * @return array<Statement>
	 */
	private function normalizeListOfStatementsWithDefaults(array $items, array $defaults): array
	{
		$statements = [];
		$defaultsMerged = false;

		foreach ($items as $item) {
			if (!$defaultsMerged && '@default' === $item) {
				foreach ($defaults as $default) {
					$statements[] = $default;
				}

				$defaultsMerged = true;

				continue;
			}

			if (!$item instanceof Statement) {
				$item = new Statement($item);
			}

			$statements[] = $item;
		}

		return $statements;
	}

	private function registerImageServerPresenter(): void
	{
		if ($this->imageServerPresenterRegistered) {
			return;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('presenter.image_server'))
			->setType(ImageServerPresenter::class);

		$this->imageServerPresenterRegistered = true;
	}
}
