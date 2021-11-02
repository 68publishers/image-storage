<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\DI\CompilerExtension;
use League\Flysystem\Visibility;
use Nette\DI\Definitions\Statement;
use Intervention\Image\ImageManager;
use Nette\DI\Definitions\Definition;
use League\Flysystem\FilesystemOperator;
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
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeFactory;
use SixtyEightPublishers\ImageStorage\ImageServer\ExternalImageServerFactory;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageConsoleExtension;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Response\ResponseFactory;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\FileStorageDefinitionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Console\Configurator\CleanCommandConfiguration;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager\ImageManagerFactory;
use SixtyEightPublishers\FileStorage\Bridge\Console\Configurator\CleanCommandConfiguratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\ImageManager\ImageManagerFactoryInterface;

final class ImageStorageExtension extends CompilerExtension implements FileStorageDefinitionFactoryInterface
{
	public const DRIVER_GD = 'gd';
	public const DRIVER_IMAGICK = 'imagick';
	public const DRIVER_68PUBLISHERS_IMAGICK = '68publishers.imagick';

	public const IMAGE_SERVER_LOCAL = 'local';
	public const IMAGE_SERVER_EXTERNAL = 'external';

	/** @var string[]  */
	private $created = [];

	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'driver' => Expect::anyOf(Statement::class, self::DRIVER_GD, self::DRIVER_IMAGICK, self::DRIVER_68PUBLISHERS_IMAGICK)->default(self::DRIVER_GD)->dynamic(),
			'storages' => Expect::arrayOf(Expect::structure([
				'source_filesystem' => Expect::structure([
					'adapter' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))->required()->before(static function ($factory) {
						return $factory instanceof Statement ? $factory : new Statement($factory);
					}),
					'config' => Expect::array([
						FlysystemConfig::OPTION_VISIBILITY => Visibility::PRIVATE,
						FlysystemConfig::OPTION_DIRECTORY_VISIBILITY => Visibility::PRIVATE,
					])->mergeDefaults(TRUE),
				]),
				'server' => Expect::anyOf(self::IMAGE_SERVER_LOCAL, self::IMAGE_SERVER_EXTERNAL)->default(self::IMAGE_SERVER_LOCAL),

				'no_image' => Expect::arrayOf('string|null')->default([
					'default' => NULL,
				])->mergeDefaults(TRUE),
				'no_image_patterns' => Expect::arrayOf('string'),
				'presets' => Expect::arrayOf('array'),

				'modifiers' => Expect::listOf('string|' . Statement::class)
					->mergeDefaults(FALSE)
					->before(static function (array $items) {
						return array_map(static function ($item) {
							return $item instanceof Statement ? $item : new Statement($item);
						}, $items);
					})
					->default([
						new Statement(Modifier\Original::class),
						new Statement(Modifier\Height::class),
						new Statement(Modifier\Width::class),
						new Statement(Modifier\AspectRatio::class),
						new Statement(Modifier\Fit::class),
						new Statement(Modifier\PixelDensity::class),
						new Statement(Modifier\Orientation::class),
						new Statement(Modifier\Quality::class),
					]),

				'applicators' => Expect::listOf('string|' . Statement::class)
					->mergeDefaults(FALSE)
					->before(static function (array $items) {
						return array_map(static function ($item) {
							return $item instanceof Statement ? $item : new Statement($item);
						}, $items);
					})
					->default([
						new Statement(Applicator\Orientation::class),
						new Statement(Applicator\Resize::class),
						new Statement(Applicator\Format::class), # must be last
					]),

				'validators' => Expect::listOf('string|' . Statement::class)
					->mergeDefaults(FALSE)
					->before(static function (array $items) {
						return array_map(static function ($item) {
							return $item instanceof Statement ? $item : new Statement($item);
						}, $items);
					})
					->default([
						new Statement(Validator\AllowedResolutionValidator::class),
						new Statement(Validator\AllowedPixelDensityValidator::class),
						new Statement(Validator\AllowedQualityValidator::class),
					]),
			])),
		]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\RuntimeException
	 */
	public function loadConfiguration(): void
	{
		if (0 >= count($this->compiler->getExtensions(FileStorageExtension::class))) {
			throw new RuntimeException(sprintf(
				'The extension %s can be used only with %s.',
				static::class,
				FileStorageExtension::class
			));
		}

		$builder = $this->getContainerBuilder();

		# Image manager
		$builder->addDefinition($this->prefix('image_manager_factory'))
			->setAutowired(FALSE)
			->setType(ImageManagerFactoryInterface::class)
			->setFactory(ImageManagerFactory::class);

		$builder->addDefinition($this->prefix('image_manager'))
			->setType(ImageManager::class)
			->setFactory([$this->prefix('@image_manager_factory'), 'create'], [
				['driver' => $this->config->driver],
			]);

		# Modifier collection factory
		$builder->addFactoryDefinition($this->prefix('modifiers.modifier_collection_factory'))
			->setAutowired(FALSE)
			->setImplement(ModifierCollectionFactoryInterface::class)
			->getResultDefinition()
			->setFactory(ModifierCollection::class);

		# Preset collection factory
		$builder->addFactoryDefinition($this->prefix('modifiers.preset_collection_factory'))
			->setAutowired(FALSE)
			->setImplement(PresetCollectionFactoryInterface::class)
			->getResultDefinition()
			->setFactory(PresetCollection::class);

		# Modifier facade factory
		$builder->addDefinition($this->prefix('modifiers.modifier_facade_factory'))
			->setAutowired(FALSE)
			->setType(ModifierFacadeFactoryInterface::class)
			->setFactory(ModifierFacadeFactory::class, [
				$this->prefix('@modifiers.preset_collection_factory'),
				$this->prefix('@modifiers.modifier_collection_factory'),
			]);

		# Responsive - srcset generator factory
		$builder->addFactoryDefinition($this->prefix('responsive.srcset_generator_factory'))
			->setAutowired(FALSE)
			->setImplement(SrcSetGeneratorFactoryInterface::class);

		# Image server - response factory
		$builder->addDefinition($this->prefix('image_server_response_factory'))
			->setAutowired(FALSE)
			->setType(ResponseFactoryInterface::class)
			->setFactory(ResponseFactory::class);

		# Custom storage cleaner
		$builder->addDefinition($this->prefix('storage_cleaner'))
			->setAutowired(FALSE)
			->setType(StorageCleanerInterface::class)
			->setFactory(StorageCleaner::class);

		# Console - extends clean command if the FileStorageConsoleExtension is registered
		if (0 < count($this->compiler->getExtensions(FileStorageConsoleExtension::class))) {
			$builder->addDefinition($this->prefix('configurator.clean_command'))
				->setType(CleanCommandConfiguratorInterface::class)
				->setFactory(CleanCommandConfiguration::class)
				->addTag(FileStorageConsoleExtension::TAG_CLEAN_COMMAND_CONFIGURATOR)
				->setAutowired(FALSE);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\RuntimeException
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$diff = array_diff(array_keys($this->config->storages), $this->created);

		if (0 < count($diff)) {
			throw new RuntimeException(sprintf(
				'Missing definition for storage with a name "%s" in configuration for the extension %s.',
				array_shift($diff),
				FileStorageExtension::class
			));
		}

		/** @var \Nette\DI\Definitions\ServiceDefinition $storageCleanerDecorator */
		$storageCleanerDecorator = $builder->getDefinition($this->prefix('storage_cleaner'));
		$defaultStorageCleaner = $builder->getDefinitionByType(StorageCleanerInterface::class);

		$storageCleanerDecorator->setArguments([$defaultStorageCleaner]);
		$storageCleanerDecorator->setAutowired(TRUE);
		$defaultStorageCleaner->setAutowired(FALSE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function canCreateFileStorage(string $name, object $config): bool
	{
		return isset($this->config->storages[$name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFileStorage(string $name, object $config): Definition
	{
		if (!$this->canCreateFileStorage($name, $config)) {
			throw new RuntimeException(sprintf(
				'Can\'t create image storage with names "%s".',
				$name
			));
		}

		$builder = $this->getContainerBuilder();
		$imageStorageConfig = $this->config->storages[$name];
		$this->created[] = $name;

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
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('config.' . $name))
			->setType(ConfigInterface::class)
			->setFactory(Config::class, [$config->config])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('modifier_facade.' . $name))
			->setType(ModifierFacadeInterface::class)
			->setFactory(new Statement([$this->prefix('@modifiers.modifier_facade_factory'), 'create'], [
				$this->prefix('@config.' . $name),
			]))
			->addSetup('setModifiers', [$imageStorageConfig->modifiers])
			->addSetup('setPresets', [$imageStorageConfig->presets])
			->addSetup('setApplicators', [$imageStorageConfig->applicators])
			->addSetup('setValidators', [$imageStorageConfig->validators])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('resource_factory.' . $name))
			->setType(ResourceFactoryInterface::class)
			->setFactory(ResourceFactory::class, [
				$this->prefix('@filesystem.' . $name),
				$this->prefix('@image_manager'),
				$this->prefix('@modifier_facade.' . $name),
			])
			->setAutowired(FALSE);

		# signature enabled
		if (!empty($config->config[Config::SIGNATURE_KEY] ?? '')) {
			$signatureStrategyDefinition = $builder->addDefinition($this->prefix('signature_strategy.' . $name))
				->setType(SignatureStrategyInterface::class)
				->setFactory(SignatureStrategy::class, [
					$this->prefix('@config.' . $name),
				])
				->setAutowired(FALSE);
		}

		$builder->addDefinition($this->prefix('link_generator.' . $name))
			->setType(LinkGeneratorInterface::class)
			->setFactory(LinkGenerator::class, [
				$this->prefix('@config.' . $name),
				$this->prefix('@modifier_facade.' . $name),
				$this->prefix('@responsive.srcset_generator_factory'),
				$signatureStrategyDefinition ?? NULL,
			])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('image_persister.' . $name))
			->setType(ImagePersisterInterface::class)
			->setFactory(ImagePersister::class, [
				$this->prefix('@filesystem.' . $name),
				$this->prefix('@config.' . $name),
				$this->prefix('@modifier_facade.' . $name),
			])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('info_factory.' . $name))
			->setType(InfoFactoryInterface::class)
			->setFactory(InfoFactory::class, [
				$this->prefix('@modifier_facade.' . $name),
				$this->prefix('@link_generator.' . $name),
				$name,
			])
			->setAutowired(FALSE);

		$defaultNoImage = $imageStorageConfig->no_image['default'];
		unset($imageStorageConfig->no_image['default']);

		$builder->addDefinition($this->prefix('no_image_config.' . $name))
			->setType(NoImageConfigInterface::class)
			->setFactory(NoImageConfig::class, [
				$defaultNoImage,
				$imageStorageConfig->no_image,
				$imageStorageConfig->no_image_patterns,
			])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('no_image_resolver.' . $name))
			->setType(NoImageResolverInterface::class)
			->setFactory(NoImageResolver::class, [
				$this->prefix('@info_factory.' . $name),
				$this->prefix('@no_image_config.' . $name),
			])
			->setAutowired(FALSE);

		$imageServerDefinition = $builder->addDefinition($this->prefix('image_server_factory.' . $name))
			->setType(ImageServerFactoryInterface::class)
			->setAutowired(FALSE);

		switch ($imageStorageConfig->server) {
			case self::IMAGE_SERVER_LOCAL:
				$imageServerDefinition->setFactory(LocalImageServerFactory::class, [$this->prefix('@image_server_response_factory')]);

				break;
			case self::IMAGE_SERVER_EXTERNAL:
				$imageServerDefinition->setFactory(ExternalImageServerFactory::class);

				break;
		}

		return $builder->addDefinition($this->prefix('image_storage.' . $name))
			->setType(ImageStorageInterface::class)
			->setFactory(ImageStorage::class, [
				$name,
				$this->prefix('@config.' . $name),
				$this->prefix('@resource_factory.' . $name),
				$this->prefix('@link_generator.' . $name),
				$this->prefix('@image_persister.' . $name),
				$this->prefix('@no_image_resolver.' . $name),
				$this->prefix('@info_factory.' . $name),
				$imageServerDefinition,
			])
			->setAutowired(FALSE);
	}
}
