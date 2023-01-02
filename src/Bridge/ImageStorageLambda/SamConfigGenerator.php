<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use ReflectionProperty;
use ReflectionException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Filesystem\AdapterProviderInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack\StackInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\ParameterOverrides;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderFactoryInterface;

final class SamConfigGenerator implements SamConfigGeneratorInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderFactoryInterface  */
	private $tomlConfigBuilderFactory;

	/** @var string  */
	private $outputDir;

	/** @var \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack\StackInterface[]  */
	private $stacks;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderFactoryInterface $tomlConfigBuilderFactory
	 * @param string                                                                                                 $outputDir
	 * @param array                                                                                                  $stacks
	 */
	public function __construct(TomlConfigBuilderFactoryInterface $tomlConfigBuilderFactory, string $outputDir, array $stacks)
	{
		$this->tomlConfigBuilderFactory = $tomlConfigBuilderFactory;
		$this->outputDir = $outputDir;

		foreach ($stacks as $stack) {
			$this->addStack($stack);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasStackForStorage(ImageStorageInterface $imageStorage): bool
	{
		return isset($this->stacks[$imageStorage->getName()]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ReflectionException
	 */
	public function generateForStorage(ImageStorageInterface $imageStorage): string
	{
		$stack = $this->stacks[$imageStorage->getName()] ?? null;

		if (null === $stack) {
			throw new InvalidArgumentException(sprintf(
				'Missing stack with name "%s".',
				$imageStorage->getName()
			));
		}

		# Bucket names
		$buckets = [
			ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => $stack->getSourceBucketName(),
			ImagePersisterInterface::FILESYSTEM_NAME_CACHE => $stack->getCacheBucketName(),
		];

		$missingBuckets = array_keys(array_filter($buckets, static function ($bucketName) {
			return empty($bucketName);
		}));

		if (0 < count($missingBuckets)) {
			$buckets = array_merge($buckets, $this->detectBucketNamesFromFilesystem($imageStorage->getFilesystem(), $missingBuckets));
		}

		# Create TOML builder and fill it with configured properties
		$builder = $this->tomlConfigBuilderFactory->create();

		foreach ($stack->getValues() as $k => $v) {
			$builder->withProperty((string) $k, $v);
		}

		# Create a "parameter_overrides" property
		$parameterOverrides = new ParameterOverrides();
		$config = $imageStorage->getConfig();
		$noImageConfig = $imageStorage->getNoImageConfig();
		$noImages = $noImagePatterns = [];

		if (null !== $noImageConfig->getDefaultPath()) {
			$noImages[] = 'default::' . $noImageConfig->getDefaultPath();
		}

		foreach ($noImageConfig->getPaths() as $noImageName => $path) {
			$noImages[] = $noImageName . '::' . $path;
		}

		foreach ($noImageConfig->getPatterns() as $noImageName => $pattern) {
			$noImagePatterns[] = $noImageName . '::' . $pattern;
		}

		$parameterOverrides['BasePath'] = $config[Config::BASE_PATH];
		$parameterOverrides['ModifierSeparator'] = $config[Config::MODIFIER_SEPARATOR];
		$parameterOverrides['ModifierAssigner'] = $config[Config::MODIFIER_ASSIGNER];
		$parameterOverrides['VersionParameterName'] = $config[Config::VERSION_PARAMETER_NAME];
		$parameterOverrides['SignatureParameterName'] = $config[Config::SIGNATURE_PARAMETER_NAME];
		$parameterOverrides['SignatureKey'] = $config[Config::SIGNATURE_KEY];
		$parameterOverrides['SignatureAlgorithm'] = $config[Config::SIGNATURE_ALGORITHM];
		$parameterOverrides['AllowedPixelDensity'] = $config[Config::ALLOWED_PIXEL_DENSITY];
		$parameterOverrides['AllowedResolutions'] = $config[Config::ALLOWED_RESOLUTIONS];
		$parameterOverrides['AllowedQualities'] = $config[Config::ALLOWED_QUALITIES];
		$parameterOverrides['EncodeQuality'] = $config[Config::ENCODE_QUALITY];
		$parameterOverrides['SourceBucketName'] = $buckets[ImagePersisterInterface::FILESYSTEM_NAME_SOURCE];
		$parameterOverrides['CacheBucketName'] = $buckets[ImagePersisterInterface::FILESYSTEM_NAME_CACHE];
		$parameterOverrides['CacheMaxAge'] = $config[Config::CACHE_MAX_AGE];
		$parameterOverrides['NoImages'] = $noImages;
		$parameterOverrides['NoImagePatterns'] = $noImagePatterns;

		$builder->setParameterOverrides($parameterOverrides);

		# Build and write it!
		$toml = $builder->buildToml();

		return $this->write($toml->getTomlString(), $stack->getName());
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack\StackInterface $stack
	 *
	 * @return void
	 */
	private function addStack(StackInterface $stack): void
	{
		$this->stacks[$stack->getName()] = $stack;
	}

	/**
	 * @param string $content
	 * @param string $name
	 *
	 * @return string
	 */
	private function write(string $content, string $name): string
	{
		$filename = sprintf(
			'%s/%s/samconfig.toml',
			rtrim($this->outputDir, '/'),
			$name
		);

		$dir = dirname($filename);

		if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
			throw new InvalidStateException(sprintf(
				'Unable to create directory "%s".',
				$dir
			));
		}

		if (false === @file_put_contents($filename, $content)) {
			throw new InvalidStateException(sprintf(
				'Unable to write file "%s". ',
				$filename
			));
		}

		return (string) realpath($filename);
	}

	/**
	 * @param \League\Flysystem\FilesystemOperator $filesystemOperator
	 * @param array                                $prefixes
	 *
	 * @return array
	 * @throws ReflectionException
	 */
	private function detectBucketNamesFromFilesystem(FilesystemOperator $filesystemOperator, array $prefixes): array
	{
		if (empty($prefixes)) {
			return [];
		}

		if (!$filesystemOperator instanceof AdapterProviderInterface) {
			throw new InvalidStateException(sprintf(
				'Can\'t detect bucket names from a filesystem because the filesystem must be implementor of %s',
				AdapterProviderInterface::class
			));
		}

		$buckets = [];

		foreach ($prefixes as $prefix) {
			$adapter = $filesystemOperator->getAdapter($prefix);

			if (!$adapter instanceof AwsS3V3Adapter) {
				throw new InvalidStateException(sprintf(
					'Adapter must be instance of %s.',
					AwsS3V3Adapter::class
				));
			}

			$reflectionProperty = new ReflectionProperty(AwsS3V3Adapter::class, 'bucket');

			$reflectionProperty->setAccessible(true);

			$buckets[$prefix] = $reflectionProperty->getValue($adapter);
		}

		return $buckets;
	}
}
