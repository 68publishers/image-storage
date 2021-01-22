<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\DI\CompilerExtension;
use Yosymfony\Toml\TomlBuilder;
use Nette\DI\Helpers as DIHelpers;
use Nette\DI\Definitions\Statement;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack\Stack;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGenerator;
use SixtyEightPublishers\ImageStorage\Bridge\Console\Command\DumpLambdaConfigCommand;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilder;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderFactoryInterface;

final class ImageStorageLambdaExtension extends CompilerExtension
{
	public const CAPABILITY_IAM = TomlConfigBuilder::CAPABILITY_IAM;
	public const CAPABILITY_NAMED_IAM = TomlConfigBuilder::CAPABILITY_NAMED_IAM;

	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		$stack = Expect::structure([
			'stack_name' => Expect::string()->required(),
			's3_bucket' => Expect::string()->required(),
			'region' => Expect::string()->required(),
			'version' => Expect::float(1.0),
			's3_prefix' => Expect::string()->nullable(), # an option "stack_name" is used by default
			'confirm_changeset' => Expect::bool(FALSE),
			'capabilities' => Expect::anyOf(self::CAPABILITY_IAM, self::CAPABILITY_NAMED_IAM)->default(self::CAPABILITY_IAM),

			'source_bucket_name' => Expect::string()->nullable()->dynamic(), # detected automatically from AwsS3V3Adapter by default
			'cache_bucket_name' => Expect::string()->nullable()->dynamic(), # detected automatically from AwsS3V3Adapter by default
		]);

		$stack->before(static function (array $stack) {
			if (empty($stack['s3_prefix'] ?? '') && !empty($stack['stack_name'] ?? '')) {
				$stack['s3_prefix'] = $stack['stack_name'];
			}

			return $stack;
		});

		return Expect::structure([
			'output_dir' => Expect::string('%appDir%/config/image-storage-lambda')->dynamic()->before(function ($dir) {
				return is_string($dir) ? DIHelpers::expand($dir, $this->getContainerBuilder()->parameters) : $dir;
			}),
			'stacks' => Expect::arrayOf($stack),
		]);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\RuntimeException
	 */
	public function loadConfiguration(): void
	{
		if (0 >= count($this->compiler->getExtensions(ImageStorageExtension::class))) {
			throw new RuntimeException(sprintf(
				'The extension %s can be used only with %s.',
				static::class,
				ImageStorageExtension::class
			));
		}

		if (!class_exists(TomlBuilder::class)) {
			throw new RuntimeException('Please require a package yosymfony/toml in your project.');
		}

		if (!class_exists(AwsS3V3Adapter::class)) {
			throw new RuntimeException('Please require a package league/flysystem-aws-s3-v3 in your project.');
		}

		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('toml_config_builder_factory'))
			->setAutowired(FALSE)
			->setImplement(TomlConfigBuilderFactoryInterface::class)
			->getResultDefinition()
			->setFactory(TomlConfigBuilder::class);

		$builder->addDefinition($this->prefix('sam_config_generator'))
			->setType(SamConfigGeneratorInterface::class)
			->setFactory(SamConfigGenerator::class, [
				$this->prefix('@toml_config_builder_factory'),
				$this->config->output_dir,
				$this->createStacks(),
			]);

		$builder->addDefinition($this->prefix('command.dump_lambda_config'))
			->setType(DumpLambdaConfigCommand::class);
	}

	/**
	 * @return array
	 */
	private function createStacks(): array
	{
		$stacks = [];

		foreach ($this->config->stacks as $stackName => $stackConfig) {
			$stackConfig = (array) $stackConfig;
			$sourceBucketName = $stackConfig['source_bucket_name'] ?? NULL;
			$cacheBucketName = $stackConfig['cache_bucket_name'] ?? NULL;

			if (array_key_exists('source_bucket_name', $stackConfig)) {
				unset($stackConfig['source_bucket_name']);
			}

			if (array_key_exists('cache_bucket_name', $stackConfig)) {
				unset($stackConfig['cache_bucket_name']);
			}

			$stacks[] = new Statement(Stack::class, [
				$stackName,
				$stackConfig,
				$sourceBucketName,
				$cacheBucketName,
			]);
		}

		return $stacks;
	}
}
