<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder;

use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Yosymfony\Toml\TomlBuilder;

final class TomlConfigBuilder implements TomlConfigBuilderInterface
{
	/** @var array  */
	private $properties;

	/**
	 * @param array $properties
	 */
	public function __construct(array $properties = [])
	{
		$this->properties = $properties;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildToml(): TomlBuilder
	{
		$properties = $this->validateAndNormalizeProperties();

		# @todo: Workaround, related issue: https://github.com/yosymfony/toml/issues/29
		$toml = new class extends TomlBuilder {
			protected function dumpValue($val): string
			{
				if (is_float($val)) {
					$result = (string) $val;

					return $val != floor($val) ? $result : $result . '.0';
				}

				return parent::dumpValue($val);
			}
		};

		$toml->addComment(' Generated by 68publishers/image-storage')
			->addValue('version', $properties->version)
			->addTable('default.deploy.parameters')
			->addValue('stack_name', $properties->stack_name)
			->addValue('s3_bucket', $properties->s3_bucket)
			->addValue('s3_prefix', $properties->s3_prefix)
			->addValue('region', $properties->region)
			->addValue('confirm_changeset', $properties->confirm_changeset)
			->addValue('capabilities', $properties->capabilities);

		if (!empty($properties->parameter_overrides)) {
			$toml->addValue('parameter_overrides', $properties->parameter_overrides);
		}

		return $toml;
	}

	/**
	 * {@inheritDoc}
	 */
	public function withProperty(string $name, $value): TomlConfigBuilderInterface
	{
		$this->properties[$name] = $value;

		return $this;
	}

	/**
	 * @param float $version
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setVersion(float $version): TomlConfigBuilderInterface
	{
		return $this->withProperty('version', $version);
	}

	/**
	 * @param string $stackName
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setStackName(string $stackName): TomlConfigBuilderInterface
	{
		return $this->withProperty('stack_name', $stackName);
	}

	/**
	 * @param string $s3Bucket
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setS3Bucket(string $s3Bucket): TomlConfigBuilderInterface
	{
		return $this->withProperty('s3_bucket', $s3Bucket);
	}

	/**
	 * @param string $s3Prefix
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setS3Prefix(string $s3Prefix): TomlConfigBuilderInterface
	{
		return $this->withProperty('s3_prefix', $s3Prefix);
	}

	/**
	 * @param string $region
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setRegion(string $region): TomlConfigBuilderInterface
	{
		return $this->withProperty('region', $region);
	}

	/**
	 * @param bool $confirmChangeSet
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setConfirmChangeSet(bool $confirmChangeSet): TomlConfigBuilderInterface
	{
		return $this->withProperty('confirm_changeset', $confirmChangeSet);
	}

	/**
	 * @param string $capabilities
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setCapabilities(string $capabilities): TomlConfigBuilderInterface
	{
		return $this->withProperty('capabilities', $capabilities);
	}

	/**
	 * @param string|\SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\ParameterOverrides $parameterOverrides
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function setParameterOverrides($parameterOverrides): TomlConfigBuilderInterface
	{
		return $this->withProperty('parameter_overrides', $parameterOverrides);
	}

	/**
	 * @return object
	 */
	private function validateAndNormalizeProperties(): object
	{
		$processor = new Processor();
		$schema = Expect::structure([
			'version' => Expect::float(1.0),
			'stack_name' => Expect::string()->required(),
			's3_bucket' => Expect::string()->required(),
			's3_prefix' => Expect::string()->required(),
			'region' => Expect::string()->required(),
			'confirm_changeset' => Expect::bool(false),
			'capabilities' => Expect::anyOf(self::CAPABILITY_IAM, self::CAPABILITY_NAMED_IAM)->default(self::CAPABILITY_IAM),
			'parameter_overrides' => Expect::type('string|' . ParameterOverrides::class)->castTo('string'),
		]);

		return $processor->process($schema, $this->properties);
	}
}
