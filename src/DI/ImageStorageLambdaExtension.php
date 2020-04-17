<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\DI;

use Nette;
use League;
use Yosymfony;
use SixtyEightPublishers;

final class ImageStorageLambdaExtension extends Nette\DI\CompilerExtension
{
	/** @var array  */
	private $defaults = [
		'output_dir' => '%appDir%/config/image-storage-lambda',
		'stacks' => [], # a key must identical with an image-storage's name
	];

	/** @var array  */
	private $stackDefaults = [
		'stack_name' => NULL, # required
		's3_bucket' => NULL, # required
		'version' => 1.0, # optional
		's3_prefix' => NULL, # optional, a stack_name option is used by default
		'region' => NULL, # optional, a region from a S3 client is used by default
		'confirm_changeset' => FALSE,
		'capabilities' => 'CAPABILITY_IAM', # CAPABILITY_IAM or CAPABILITY_NAMED_IAM
	];

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	public function loadConfiguration(): void
	{
		if (!class_exists(Yosymfony\Toml\TomlBuilder::class)) {
			throw new Nette\Utils\AssertionException('Please require a package yosymfony/toml in your project.');
		}

		if (!class_exists(League\Flysystem\AwsS3v3\AwsS3Adapter::class)) {
			throw new Nette\Utils\AssertionException('Please require a package league/flysystem-aws-s3-v3 in your project.');
		}

		$builder = $this->getContainerBuilder();
		$config = Nette\DI\Helpers::expand($this->validateConfig($this->defaults), $builder->parameters);
		$stacks = $config['stacks'];

		$builder->addDefinition($this->prefix('image_storage_config_generator'))
			->setType(SixtyEightPublishers\ImageStorage\SamConfig\IImageStorageConfigGenerator::class)
			->setFactory(SixtyEightPublishers\ImageStorage\SamConfig\ImageStorageConfigGenerator::class);

		$dumpCommand = $builder->addDefinition($this->prefix('command.dump_lambda_config'))
			->setType(SixtyEightPublishers\ImageStorage\Console\DumpLambdaConfigCommand::class)
			->setArguments([
				'outputDir' => $config['output_dir'],
			]);

		foreach ($stacks as $name => $stack) {
			$stack = $this->validateConfig($this->stackDefaults, $stack);

			Nette\Utils\Validators::assertField($stack, 'stack_name', 'string');
			Nette\Utils\Validators::assertField($stack, 's3_bucket', 'string');

			$dumpCommand->addSetup('addStack', [
				'name' => $name,
				'values' => $stack,
			]);
		}
	}
}
