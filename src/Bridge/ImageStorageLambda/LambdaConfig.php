<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use stdClass;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Schema\Processor;
use function assert;
use function is_array;

final class LambdaConfig
{
	public const CAPABILITY_IAM = 'CAPABILITY_IAM';
	public const CAPABILITY_NAMED_IAM = 'CAPABILITY_NAMED_IAM';

	public ?string $stack_name = null; # the image storage name is used as the default value

	public float $version;

	public string $s3_bucket;

	public ?string $s3_prefix = null; # the $stack_name is used as the default value

	public string $region;

	public bool $confirm_changeset;

	public string $capabilities;

	public ParameterOverrides $parameter_overrides;

	public ?string $source_bucket_name = null;  # detected automatically from AwsS3V3Adapter by default

	public ?string $cache_bucket_name = null; # detected automatically from AwsS3V3Adapter by default

	/**
	 * @param array<string, mixed>|stdClass $values
	 *
	 * @return static
	 */
	public static function fromValues(array|stdClass $values): self
	{
		$config = (new Processor())->process(self::createSchema(), (array) $values);
		assert($config instanceof self);

		return $config;
	}

	public static function createSchema(): Schema
	{
		$schema = Expect::structure([
			'stack_name' => Expect::string()
				->nullable()
				->dynamic(),
			'version' => Expect::float(1.0)
				->dynamic(),
			's3_bucket' => Expect::string()
				->required()
				->dynamic(),
			's3_prefix' => Expect::string()
				->nullable()
				->dynamic(),
			'region' => Expect::string()
				->required()
				->dynamic(),
			'confirm_changeset' => Expect::bool(false)
				->dynamic(),
			'capabilities' => Expect::anyOf(self::CAPABILITY_IAM, self::CAPABILITY_NAMED_IAM)
				->default(self::CAPABILITY_IAM)
				->dynamic(),
			'parameter_overrides' => Expect::anyOf(
				Expect::type(ParameterOverrides::class),
				Expect::arrayOf(
					Expect::anyOf(
						Expect::scalar(),
						Expect::listOf(Expect::scalar())
					),
					'string'
				)
			)->default(new ParameterOverrides([]))
				->before(static fn (ParameterOverrides|array $value): ParameterOverrides => is_array($value) ? new ParameterOverrides($value) : $value),
			'source_bucket_name' => Expect::string()
				->nullable()
				->dynamic(), # detected automatically from AwsS3V3Adapter by default
			'cache_bucket_name' => Expect::string()
				->nullable()
				->dynamic(), # detected automatically from AwsS3V3Adapter by default
		]);

		$schema->before(static function (array $config): array {
			if (empty($config['s3_prefix'] ?? '') && !empty($config['stack_name'] ?? '')) {
				$config['s3_prefix'] = $config['stack_name'];
			}

			return $config;
		});

		return $schema->castTo(self::class);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'stack_name' => $this->stack_name,
			'version' => $this->version,
			's3_bucket' => $this->s3_bucket,
			's3_prefix' => $this->s3_prefix,
			'region' => $this->region,
			'confirm_changeset' => $this->confirm_changeset,
			'capabilities' => $this->capabilities,
			'parameter_overrides' => $this->parameter_overrides->parameters,
			'source_bucket_name' => $this->source_bucket_name,
			'cache_bucket_name' => $this->cache_bucket_name,
		];
	}
}
