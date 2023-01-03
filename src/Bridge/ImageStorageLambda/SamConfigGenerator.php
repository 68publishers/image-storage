<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use ReflectionProperty;
use Yosymfony\Toml\TomlBuilder;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Filesystem\AdapterProviderInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use function count;
use function floor;
use function mkdir;
use function rtrim;
use function assert;
use function is_dir;
use function dirname;
use function sprintf;
use function is_float;
use function realpath;
use function is_string;
use function array_keys;
use function array_merge;
use function array_filter;
use function file_put_contents;

final class SamConfigGenerator implements SamConfigGeneratorInterface
{
	/**
	 * @param array<string, array<string, mixed>> $configs
	 */
	public function __construct(
		private readonly string $outputDir,
		private readonly array $configs,
	) {
	}

	public function canGenerate(ImageStorageInterface $imageStorage): bool
	{
		return isset($this->configs[$imageStorage->getName()]);
	}

	/**
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function generate(ImageStorageInterface $imageStorage): string
	{
		$lambdaConfig = $this->configs[$imageStorage->getName()] ?? null;

		if (null === $lambdaConfig) {
			throw new InvalidArgumentException(sprintf(
				'Missing config with the name "%s".',
				$imageStorage->getName()
			));
		}

		$lambdaConfig = LambdaConfig::fromValues($lambdaConfig);
		$buckets = [
			ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => $lambdaConfig->source_bucket_name,
			ImagePersisterInterface::FILESYSTEM_NAME_CACHE => $lambdaConfig->cache_bucket_name,
		];
		$missingBuckets = array_keys(array_filter($buckets, static fn (?string $bucketName): bool => null === $bucketName));

		if (0 < count($missingBuckets)) {
			$buckets = array_merge(
				$buckets,
				$this->detectBucketNamesFromFilesystem($imageStorage->getFilesystem(), $missingBuckets)
			);
		}

		assert(is_string($buckets[ImagePersisterInterface::FILESYSTEM_NAME_SOURCE]) && is_string($buckets[ImagePersisterInterface::FILESYSTEM_NAME_CACHE]));

		$lambdaConfig->parameter_overrides = $lambdaConfig->parameter_overrides->withMissingParameters(
			$this->createDefaultParameterOverrides(
				$imageStorage,
				$buckets[ImagePersisterInterface::FILESYSTEM_NAME_SOURCE],
				$buckets[ImagePersisterInterface::FILESYSTEM_NAME_CACHE]
			)
		);

		$toml = $this->createToml($lambdaConfig, $imageStorage->getName());

		return $this->write($toml, $lambdaConfig->stack_name ?? $imageStorage->getName());
	}

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
	 * @param array<string> $prefixes
	 *
	 * @return array<string, string>
	 */
	private function detectBucketNamesFromFilesystem(FilesystemOperator $filesystemOperator, array $prefixes): array
	{
		if (!$filesystemOperator instanceof AdapterProviderInterface) {
			throw new InvalidStateException(sprintf(
				'Can\'t detect bucket names from a filesystem because the filesystem must be an implementor of %s.',
				AdapterProviderInterface::class
			));
		}

		$buckets = [];

		foreach ($prefixes as $prefix) {
			$adapter = $filesystemOperator->getAdapter($prefix);

			if (!$adapter instanceof AwsS3V3Adapter) {
				throw new InvalidStateException(sprintf(
					'Adapter must be an instance of %s.',
					AwsS3V3Adapter::class
				));
			}

			$reflectionProperty = new ReflectionProperty(AwsS3V3Adapter::class, 'bucket');
			$bucket = $reflectionProperty->getValue($adapter);
			assert(is_string($bucket));

			$buckets[$prefix] = $bucket;
		}

		return $buckets;
	}

	/**
	 * @return array<string, scalar|array<scalar>>
	 */
	private function createDefaultParameterOverrides(ImageStorageInterface $imageStorage, string $sourceBucketName, string $cacheBucketName): array
	{
		$imageStorageConfig = $imageStorage->getConfig();
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

		return [
			'BasePath' => $imageStorageConfig[ConfigInterface::BASE_PATH],
			'ModifierSeparator' => $imageStorageConfig[Config::MODIFIER_SEPARATOR],
			'ModifierAssigner' => $imageStorageConfig[Config::MODIFIER_ASSIGNER],
			'VersionParameterName' => $imageStorageConfig[ConfigInterface::VERSION_PARAMETER_NAME],
			'SignatureParameterName' => $imageStorageConfig[Config::SIGNATURE_PARAMETER_NAME],
			'SignatureKey' => $imageStorageConfig[Config::SIGNATURE_KEY],
			'SignatureAlgorithm' => $imageStorageConfig[Config::SIGNATURE_ALGORITHM],
			'AllowedPixelDensity' => $imageStorageConfig[Config::ALLOWED_PIXEL_DENSITY],
			'AllowedResolutions' => $imageStorageConfig[Config::ALLOWED_RESOLUTIONS],
			'AllowedQualities' => $imageStorageConfig[Config::ALLOWED_QUALITIES],
			'EncodeQuality' => $imageStorageConfig[Config::ENCODE_QUALITY],
			'SourceBucketName' => $sourceBucketName,
			'CacheBucketName' => $cacheBucketName,
			'CacheMaxAge' => $imageStorageConfig[Config::CACHE_MAX_AGE],
			'NoImages' => $noImages,
			'NoImagePatterns' => $noImagePatterns,
		];
	}

	private function createToml(LambdaConfig $lambdaConfig, string $imageStorageName): string
	{
		# @todo: Workaround, related issue: https://github.com/yosymfony/toml/issues/29 ... still not released (2.1.2023)
		$toml = new class extends TomlBuilder {
			/**
			 * @param string|int|bool|float|array<mixed, mixed> $val
			 */
			protected function dumpValue($val): string
			{
				if (is_float($val)) {
					$result = (string) $val;

					return $val !== floor($val) ? $result : $result . '.0';
				}

				return parent::dumpValue($val);
			}
		};

		$toml->addComment(' Generated by 68publishers/image-storage')
			->addValue('version', $lambdaConfig->version)
			->addTable('default.deploy.parameters')
			->addValue('stack_name', $lambdaConfig->stack_name ?? $imageStorageName)
			->addValue('s3_bucket', $lambdaConfig->s3_bucket)
			->addValue('s3_prefix', $lambdaConfig->s3_prefix ?? $imageStorageName)
			->addValue('region', $lambdaConfig->region)
			->addValue('confirm_changeset', $lambdaConfig->confirm_changeset)
			->addValue('capabilities', $lambdaConfig->capabilities);

		$parameterOverrides = (string) $lambdaConfig->parameter_overrides;

		if (!empty($parameterOverrides)) {
			$toml->addValue('parameter_overrides', $parameterOverrides);
		}

		return $toml->getTomlString();
	}
}
