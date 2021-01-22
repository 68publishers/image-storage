<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder;

use Yosymfony\Toml\TomlBuilder;

interface TomlConfigBuilderInterface
{
	public const PROPERTY_VERSION = 'version';
	public const PROPERTY_STACK_NAME = 'stack_name';
	public const PROPERTY_S3_BUCKET = 's3_bucket';
	public const PROPERTY_S3_PREFIX = 's3_prefix';
	public const PROPERTY_REGION = 'region';
	public const PROPERTY_CONFIRM_CHANGESET = 'confirm_changeset';
	public const PROPERTY_CAPABILITIES = 'capabilities';
	public const PROPERTY_PARAMETER_OVERRIDES = 'parameter_overrides';

	public const CAPABILITY_IAM = 'CAPABILITY_IAM';
	public const CAPABILITY_NAMED_IAM = 'CAPABILITY_NAMED_IAM';

	/**
	 * @return \Yosymfony\Toml\TomlBuilder
	 */
	public function buildToml(): TomlBuilder;

	/**
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return static
	 */
	public function setProperty(string $name, $value): self;
}
