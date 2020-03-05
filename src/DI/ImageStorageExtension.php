<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DI;

use Latte;
use Nette;
use League;
use Intervention;
use SixtyEightPublishers;

final class ImageStorageExtension extends Nette\DI\CompilerExtension
{
	public const MODIFIERS = [
		SixtyEightPublishers\ImageStorage\Modifier\Original::class,
		SixtyEightPublishers\ImageStorage\Modifier\Height::class,
		SixtyEightPublishers\ImageStorage\Modifier\Width::class,
		SixtyEightPublishers\ImageStorage\Modifier\PixelDensity::class,
		SixtyEightPublishers\ImageStorage\Modifier\Orientation::class,
		SixtyEightPublishers\ImageStorage\Modifier\Quality::class,
	];

	public const APPLICATORS = [
		SixtyEightPublishers\ImageStorage\Modifier\Applicator\Resize::class,
		SixtyEightPublishers\ImageStorage\Modifier\Applicator\Orientation::class,
		SixtyEightPublishers\ImageStorage\Modifier\Applicator\Format::class,
	];

	public const VALIDATORS = [
		SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedResolutionValidator::class,
		SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedPixelDensityValidator::class,
		SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedQualityValidator::class,
	];

	public const    SERVER_LOCAL = 'local',
					SERVER_EXTERNAL = 'external';

	/** @var array  */
	private $defaults = [
		'env' => [],
		'driver' => 'gd', # gd or imagick
		'storages' => [
			# array of storage definitions, first one is default
		],
		'bridge' => [
			'doctrine_type' => FALSE,
			'latte_macros' => TRUE,
		],
	];

	/** @var array  */
	private $storageDefaults = [
		'source' => [
			'adapter' => NULL, # filesystem adapter
			'config' => [
				'visibility' => League\Flysystem\AdapterInterface::VISIBILITY_PRIVATE,
			], # filesystem config
		],
		'cache' => [
			'adapter' => NULL, # filesystem adapter
			'config' => [
				'visibility' => League\Flysystem\AdapterInterface::VISIBILITY_PUBLIC,
			], # filesystem config
		],
		'server' => self::SERVER_LOCAL,
		'signature' => NULL, # null or ISignatureStrategy statement or a string (= privateKey). The DefaultSignatureStrategy is used if a string is passed

		'modifiers' => [],  # array of IModifier
		'applicators' => [], # array of IModifierApplicator
		'validators' => [], # array of IValidator

		'presets' => [], # predefined presets
		'assets' => [], # predefined assets for synchronization

		'no_image' => [
			'default' => NULL,
		],
		'no_image_rules' => [],
	];

	/** @var NULL|array */
	private $externalAssets;

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		$storages = $config['storages'];

		if (!is_array($storages) || 0 >= count($storages)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Almost one storage must be defined in %s.storages',
				$this->name
			));
		}

		if (!in_array($config['driver'], [ 'gd', 'imagick' ], TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Invalid argument passed into %s.driver, driver %s is not supported.',
				$this->name,
				$config['driver']
			));
		}

		# environment
		$builder->addDefinition($this->prefix('env'))
			->setType(SixtyEightPublishers\ImageStorage\Config\Env::class)
			->setArguments([
				'env' => $config['env'],
			]);

		# image manager

		$builder->addDefinition($this->prefix('image_manager'))
			->setType(Intervention\Image\ImageManager::class)
			->setArguments([
				'config' => [
					'driver' => $config['driver'],
				],
			]);

		# factories from Modifier namespace

		$builder->addDefinition($this->prefix('codec_factory'))
			->setImplement(SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodecFactory::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Modifier\Codec\DefaultCodec::class);

		$builder->addDefinition($this->prefix('modifier_collection_factory'))
			->setImplement(SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollectionFactory::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection::class);

		$builder->addDefinition($this->prefix('preset_collection_factory'))
			->setImplement(SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollectionFactory::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollection::class);

		# image storage provider
		$builder->addDefinition($this->prefix('image_storage_provider'))
			->setType(SixtyEightPublishers\ImageStorage\IImageStorageProvider::class)
			->setFactory(SixtyEightPublishers\ImageStorage\ImageStorageProvider::class, [
				'defaultImageStorage' => $this->registerImageStorage(key($storages), array_shift($storages), TRUE),
				'imageStorages' => array_map(function ($config, $key) {
					return $this->registerImageStorage((string) $key, $config);
				}, $storages, array_keys($storages)),
			]);

		# srcset generator
		$builder->addDefinition($this->prefix('srcset_generator_factory'))
			->setImplement(SixtyEightPublishers\ImageStorage\Responsive\ISrcSetGeneratorFactory::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator::class);

		# latte macros enabled
		if (TRUE === $config['bridge']['latte_macros']) {
			$builder->addDefinition($this->prefix('latte.image_storage_latte_facade'))
				->setType(SixtyEightPublishers\ImageStorage\Latte\ImageStorageLatteFacade::class);
		}

		# console

		$builder->addDefinition($this->prefix('command.copy_assets'))
			->setType(SixtyEightPublishers\ImageStorage\Console\CopyAssetsCommand::class)
			->setArguments([
				'storageAssets' => $builder->findByType(SixtyEightPublishers\ImageStorage\Assets\StorageAssets::class),
			]);

		$builder->addDefinition($this->prefix('command.clean'))
			->setType(SixtyEightPublishers\ImageStorage\Console\CleanCommand::class)
			->setArguments([
				'cleaners' => $builder->findByType(SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner::class),
			]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile(): void
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		# Latte macros enabled
		if (TRUE === $config['bridge']['latte_macros']) {
			$latteFactory = $builder->getDefinition($builder->getByType(Latte\Engine::class) ?? 'nette.latteFactory');

			$latteFactory->addSetup('addProvider', [
				'name' => 'imageStorageLatteFacade',
				'value' => $this->prefix('@latte.image_storage_latte_facade'),
			]);

			$latteFactory->addSetup('?->onCompile[] = function ($engine) { ?::install($engine->getCompiler()); }', [
				'@self',
				new Nette\PhpGenerator\PhpLiteral(SixtyEightPublishers\ImageStorage\Latte\ImageStorageMacroSet::class),
			]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class): void
	{
		$config = $this->getConfig();
		$initialize = $class->methods['initialize'];

		# Doctrine type enabled
		if (TRUE === $config['bridge']['doctrine_type']) {
			$initialize->addBody('if (!Doctrine\DBAL\Types\Type::hasType(?)) { Doctrine\DBAL\Types\Type::addType(?, ?); \Doctrine\DBAL\Types\Type::getType(?)->setDependencies($this->getService(?)); }', [
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoType::NAME,
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoType::NAME,
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoType::class,
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoType::NAME,
				$this->prefix('image_storage_provider'),
			]);
		}
	}

	/**
	 * @param mixed $definition
	 *
	 * @return bool
	 */
	private function needRegister($definition): bool
	{
		return (!is_string($definition) || !Nette\Utils\Strings::startsWith($definition, '@'));
	}

	/**
	 * @param string $name
	 * @param array  $config
	 * @param bool   $autowired
	 *
	 * @return \Nette\DI\ServiceDefinition
	 * @throws \Nette\Utils\AssertionException
	 */
	private function registerImageStorage(string $name, array $config, bool $autowired = FALSE): Nette\DI\ServiceDefinition
	{
		$builder = $this->getContainerBuilder();

		$config = $this->validateConfig($this->storageDefaults, $config);
		Nette\Utils\Validators::assert($config['signature'], 'null|string|' . Nette\DI\Statement::class);

		if (!in_array($config['server'], [ self::SERVER_LOCAL, self::SERVER_EXTERNAL ], TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Server type "%s" is not supported.',
				is_object($config['server']) ? get_class($config['server']) : (string) $config['server']
			));
		}

		$filesystem = $builder->addDefinition($this->prefix($name . '.filesystem_service'))
			->setType(SixtyEightPublishers\ImageStorage\Filesystem::class)
			->setArguments([
				'source' => $this->registerFilesystem($config['source'], $name, 'source'),
				'cache' => $this->registerFilesystem($config['cache'], $name, 'cache'),
			])
			->setAutowired(FALSE);

		# Signature strategy
		if (NULL !== $config['signature'] && $this->needRegister($config['signature'])) {
			$signature = $builder->addDefinition($this->prefix($name . '.signature_strategy'))
				->setType(SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy::class)
				->setFactory($config['signature'] instanceof Nette\DI\Statement ? $config['signature'] : SixtyEightPublishers\ImageStorage\Security\DefaultSignatureStrategy::class)
				->setAutowired(FALSE);

			if (is_string($config['signature'])) {
				$signature->setArguments([
					'privateKey' => $config['signature'],
				]);
			}

			$config['signature'] = $signature;
		}

		# Modifiers
		$modifierFacade = $builder->addDefinition($this->prefix($name . '.modifier_facade'))
			->setType(SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade::class)
			->addSetup('setModifiers', [
				'modifiers' => array_map(static function ($def) {
					return $def instanceof Nette\DI\Statement ? $def : new Nette\DI\Statement($def);
				}, !empty($config['modifiers']) ? $config['modifiers'] : self::MODIFIERS),
			])
			->addSetup('setApplicators', [
				'applicators' => array_map(static function ($def) {
					return $def instanceof Nette\DI\Statement ? $def : new Nette\DI\Statement($def);
				}, !empty($config['applicators']) ? $config['applicators'] : self::APPLICATORS),
			])
			->addSetup('setValidators', [
				'validators' => array_map(static function ($def) {
					return $def instanceof Nette\DI\Statement ? $def : new Nette\DI\Statement($def);
				}, !empty($config['validators']) ? $config['validators'] : self::VALIDATORS),
			])
			->addSetup('setPresets', [
				'presets' => $config['presets'],
			])
			->setAutowired(FALSE);

		# No image
		$defaultNoImage = $config['no_image']['default'];
		unset($config['no_image']['default']);

		$noImageProvider = $builder->addDefinition($this->prefix($name . '.no_image_provider'))
			->setType(SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider::class)
			->setFactory(SixtyEightPublishers\ImageStorage\NoImage\NoImageProvider::class, [
				'defaultPath' => $defaultNoImage,
				'paths' => $config['no_image'],
			])
			->setAutowired(FALSE);

		$noImageResolver = $builder->addDefinition($this->prefix($name . '.no_image_resolver'))
			->setType(SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver::class)
			->setFactory(SixtyEightPublishers\ImageStorage\NoImage\NoImageResolver::class, [
				'noImageProvider' => $noImageProvider,
				'rules' => $config['no_image_rules'],
			])
			->setAutowired(FALSE);

		# Links & Persistence
		$linkGenerator = $builder->addDefinition($this->prefix($name . '.link_generator'))
			->setType(SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator::class)
			->setFactory(SixtyEightPublishers\ImageStorage\LinkGenerator\DefaultLinkGenerator::class, [
				'modifierFacade' => $modifierFacade,
			])
			->setAutowired(FALSE);

		$imagePersister = $builder->addDefinition($this->prefix($name . '.image_persister'))
			->setType(SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister::class)
			->setFactory(SixtyEightPublishers\ImageStorage\ImagePersister\DefaultImagePersister::class, [
				'filesystem' => $filesystem,
				'modifierFacade' => $modifierFacade,
			])
			->setAutowired(FALSE);

		# Resources
		$resourceFactory = $builder->addDefinition($this->prefix($name . '.resource_factory'))
			->setType(SixtyEightPublishers\ImageStorage\Resource\IResourceFactory::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Resource\ResourceFactory::class, [
				'filesystem' => $filesystem,
				'modifierFacade' => $modifierFacade,
			])
			->setAutowired(FALSE);

		# ImageServer
		$imageServer = $builder->addDefinition($this->prefix($name . '.image_server'))
			->setType(SixtyEightPublishers\ImageStorage\ImageServer\IImageServer::class)
			->setAutowired(FALSE);

		if (self::SERVER_LOCAL === $config['server']) {
			$imageServer->setFactory(SixtyEightPublishers\ImageStorage\ImageServer\LocalImageServer::class, [
				'noImageResolver' => $noImageResolver,
				'resourceFactory' => $resourceFactory,
				'imagePersister' => $imagePersister,
				'modifierFacade' => $modifierFacade,
			]);
		} else {
			$imageServer->setFactory(SixtyEightPublishers\ImageStorage\ImageServer\ExternalImageServer::class);
		}

		# ImageStorage
		$imageStorage = $builder->addDefinition($this->prefix($name . '.image_storage'))
			->setType(SixtyEightPublishers\ImageStorage\IImageStorage::class)
			->setFactory(SixtyEightPublishers\ImageStorage\ImageStorage::class, [
				'name' => $name,
				'linkGenerator' => $linkGenerator,
				'noImageProvider' => $noImageProvider,
				'noImageResolver' => $noImageResolver,
				'resourceFactory' => $resourceFactory,
				'imagePersister' => $imagePersister,
				'imageServer' => $imageServer,
			])
			->addSetup('setSignatureStrategy', [
				'signatureStrategy' => $config['signature'],
			])
			->setAutowired($autowired);

		# Assets
		if (is_array($config['assets']) && 0 < count($config['assets'])) {
			$storageAssets = $builder->addDefinition($this->prefix($name . '.storage_assets'))
				->setType(SixtyEightPublishers\ImageStorage\Assets\StorageAssets::class)
				->setArguments([
					'imageStorage' => $imageStorage,
				])
				->setAutowired(FALSE);

			foreach (array_merge($config['assets'], $this->getExternalAssets($name)) as $from => $to) {
				/** @noinspection PhpInternalEntityUsedInspection */
				$storageAssets->addSetup('add', [
					'from' => Nette\DI\Helpers::expand((string) $from, $builder->parameters),
					'to' => Nette\DI\Helpers::expand((string) $to, $builder->parameters),
				]);
			}
		}

		# Storage Cleaner
		$builder->addDefinition($this->prefix($name . '.storage_cleaner'))
			->setType(SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner::class)
			->setFactory(SixtyEightPublishers\ImageStorage\Cleaner\DefaultStorageCleaner::class, [
				'name' => $name,
				'filesystem' => $filesystem,
			])
			->setAutowired(FALSE);

		return $imageStorage;
	}

	/**
	 * @param array  $filesystemConfig
	 * @param string $storageName
	 * @param string $filesystemName
	 *
	 * @return \Nette\DI\ServiceDefinition
	 * @throws \Nette\Utils\AssertionException
	 */
	private function registerFilesystem(array &$filesystemConfig, string $storageName, string $filesystemName): Nette\DI\ServiceDefinition
	{
		$builder = $this->getContainerBuilder();

		Nette\Utils\Validators::assert($filesystemConfig['adapter'], 'string|' . Nette\DI\Statement::class);
		Nette\Utils\Validators::assert($filesystemConfig['config'], 'array');

		# Flysystem Adapter
		if ($this->needRegister($filesystemConfig['adapter'])) {
			$config['adapter'] = $builder->addDefinition($this->prefix($storageName . '.filesystem_adapter.' . $filesystemName))
				->setType(League\Flysystem\AdapterInterface::class)
				->setFactory($filesystemConfig['adapter'])
				->setAutowired(FALSE);
		}

		# Flysytem
		return $builder->addDefinition($this->prefix($storageName . '.filesystem.' . $filesystemName))
			->setType(League\Flysystem\FilesystemInterface::class)
			->setFactory(League\Flysystem\Filesystem::class, [
				'adapter' => $filesystemConfig['adapter'],
				'config' => $filesystemConfig['config'],
			])
			->setAutowired(FALSE);
	}

	/**
	 * @param string $imageStorageName
	 *
	 * @return array
	 */
	private function getExternalAssets(string $imageStorageName): array
	{
		if (is_array($this->externalAssets)) {
			return $this->externalAssets[$imageStorageName] ?? [];
		}

		$this->externalAssets = [];

		/** @var \SixtyEightPublishers\ImageStorage\DI\IAssetsProvider $extension */
		foreach ($this->compiler->getExtensions(IAssetsProvider::class) as $extension) {
			foreach ($extension->provideAssets() as $name => $assets) {
				$this->externalAssets[$name] = isset($this->externalAssets[$name])
					? array_merge($this->externalAssets[$name], (array) $assets)
					: (array) $assets;
			}
		}

		return $this->externalAssets[$imageStorageName] ?? [];
	}
}
