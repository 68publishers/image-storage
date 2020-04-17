<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\SamConfig;

use Nette;
use League;
use SixtyEightPublishers;

final class ImageStorageConfigGenerator implements IImageStorageConfigGenerator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config $config
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Config $config)
	{
		$this->config = $config;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\SamConfig\IImageStorageConfigGenerator **************/

	/**
	 * {@inheritDoc}
	 * @throws \Nette\Utils\AssertionException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function generate(SixtyEightPublishers\ImageStorage\IImageStorage $imageStorage, array $properties, string $outputPath): void
	{
		Nette\Utils\Validators::assertField($properties, 'stack_name', 'string');
		Nette\Utils\Validators::assertField($properties, 's3_bucket', 'string');

		$filesystem = $imageStorage->getFilesystem();
		$sourceAdapter = $filesystem->getSource()->getAdapter();
		$cacheAdapter = $filesystem->getCache()->getAdapter();

		if (!$sourceAdapter instanceof League\Flysystem\AwsS3v3\AwsS3Adapter) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'A source adapter for ImageStorage with name "%s" must be instance of %s, instance of %s is used.',
				$imageStorage->getName(),
				League\Flysystem\AwsS3v3\AwsS3Adapter::class,
				get_class($sourceAdapter)
			));
		}

		if (!$cacheAdapter instanceof League\Flysystem\AwsS3v3\AwsS3Adapter) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'A cache adapter for ImageStorage with name "%s" must be instance of %s, instance of %s is used.',
				$imageStorage->getName(),
				League\Flysystem\AwsS3v3\AwsS3Adapter::class,
				get_class($cacheAdapter)
			));
		}

		$builder = new SamConfigBuilder($properties);

		if (!isset($properties['region'])) {
			$builder->setRegion($sourceAdapter->getClient()->getRegion());
		}

		if (!isset($properties['s3_prefix'])) {
			$builder->setS3Prefix((string) $properties['stack_name']);
		}

		$parameterOverrides = new ParameterOverrides();
		$noImageConfig = $imageStorage->getNoImageConfig();
		$noImages = $noImagePatterns = [];

		if (NULL !== $noImageConfig->getDefaultPath()) {
			$noImages[] = 'default::' . $noImageConfig->getDefaultPath();
		}

		foreach ($noImageConfig->getPaths() as $noImageName => $path) {
			$noImages[] = $noImageName . '::' . $path;
		}

		foreach ($noImageConfig->getPatterns() as $noImageName => $pattern) {
			$noImagePatterns[] = $noImageName . '::' . $pattern;
		}

		$parameterOverrides['BasePath'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::BASE_PATH];
		$parameterOverrides['ModifierSeparator'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_SEPARATOR];
		$parameterOverrides['ModifierAssigner'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_ASSIGNER];
		$parameterOverrides['SignatureParameterName'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_PARAMETER_NAME];
		$parameterOverrides['SignatureKey'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_KEY];
		$parameterOverrides['SignatureAlgorithm'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_ALGORITHM];
		$parameterOverrides['AllowedPixelDensity'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ALLOWED_PIXEL_DENSITY];
		$parameterOverrides['AllowedResolutions'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ALLOWED_RESOLUTIONS];
		$parameterOverrides['AllowedQualities'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ALLOWED_QUALITIES];
		$parameterOverrides['EncodeQuality'] = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ENCODE_QUALITY];
		$parameterOverrides['SourceBucketName'] = $sourceAdapter->getBucket();
		$parameterOverrides['CacheBucketName'] = $cacheAdapter->getBucket();
		$parameterOverrides['NoImages'] = $noImages;
		$parameterOverrides['NoImagePatterns'] = $noImagePatterns;

		$builder->setParameterOverrides($parameterOverrides);

		Nette\Utils\FileSystem::write($outputPath, (string) $builder);
	}
}
