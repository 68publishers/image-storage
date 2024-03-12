<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\ImageStorageLambda;

use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\LambdaConfig;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\ParameterOverrides;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class LambdaConfigTest extends TestCase
{
    public function testConfigWithRequiredValuesOnlyShouldBeCreatedFromArray(): void
    {
        $config = LambdaConfig::fromValues([
            's3_bucket' => 'test_bucket',
            'region' => 'west',
        ]);

        $this->assertConfig(
            config: $config,
            s3Bucket: 'test_bucket',
            region: 'west',
        );
    }

    public function testConfigWithRequiredValuesOnlyShouldBeCreatedFromStdClass(): void
    {
        $config = LambdaConfig::fromValues((object) [
            's3_bucket' => 'test_bucket',
            'region' => 'west',
        ]);

        $this->assertConfig(
            config: $config,
            s3Bucket: 'test_bucket',
            region: 'west',
        );
    }

    public function testConfigWithAllValuesShouldBeCreated(): void
    {
        $config = LambdaConfig::fromValues([
            'stack_name' => 'test_stack',
            'version' => 2.5,
            's3_bucket' => 'test_bucket',
            's3_prefix' => 'test_prefix',
            'region' => 'west',
            'confirm_changeset' => true,
            'capabilities' => LambdaConfig::CAPABILITY_NAMED_IAM,
            'parameter_overrides' => new ParameterOverrides(['TEST_KEY' => 'TEST_VALUE']),
            'source_bucket_name' => 'source',
            'cache_bucket_name' => 'cache',
        ]);

        $this->assertConfig(
            config: $config,
            s3Bucket: 'test_bucket',
            region: 'west',
            stackName: 'test_stack',
            version: 2.5,
            s3Prefix: 'test_prefix',
            confirmChangeset: true,
            capabilities: LambdaConfig::CAPABILITY_NAMED_IAM,
            parameterOverrides: new ParameterOverrides(['TEST_KEY' => 'TEST_VALUE']),
            sourceBucketName: 'source',
            cacheBucketName: 'cache',
        );
    }

    public function testS3PrefixShouldBeEqualToStackName(): void
    {
        $config = LambdaConfig::fromValues([
            'stack_name' => 'test_stack',
            's3_bucket' => 'test_bucket',
            'region' => 'west',
        ]);

        $this->assertConfig(
            config: $config,
            s3Bucket: 'test_bucket',
            region: 'west',
            stackName: 'test_stack',
            s3Prefix: 'test_stack',
        );
    }

    public function testParameterOverridesOptionShouldAcceptArray(): void
    {
        $config = LambdaConfig::fromValues([
            's3_bucket' => 'test_bucket',
            'region' => 'west',
            'parameter_overrides' => [
                'KEY_A' => 'VALUE_A',
                'KEY_B' => [
                    'VALUE_B_1',
                    'VALUE_B_2',
                ],
                'KEY_C' => 15,
            ],
        ]);

        $this->assertConfig(
            config: $config,
            s3Bucket: 'test_bucket',
            region: 'west',
            parameterOverrides: new ParameterOverrides([
                'KEY_A' => 'VALUE_A',
                'KEY_B' => [
                    'VALUE_B_1',
                    'VALUE_B_2',
                ],
                'KEY_C' => 15,
            ]),
        );
    }

    public function assertConfig(
        LambdaConfig $config,
        string $s3Bucket,
        string $region,
        ?string $stackName = null,
        float $version = 1.0,
        ?string $s3Prefix = null,
        bool $confirmChangeset = false,
        string $capabilities = LambdaConfig::CAPABILITY_IAM,
        ?ParameterOverrides $parameterOverrides = new ParameterOverrides([]),
        ?string $sourceBucketName = null,
        ?string $cacheBucketName = null,
    ): void {
        Assert::same($s3Bucket, $config->s3_bucket);
        Assert::same($region, $config->region);
        Assert::same($stackName, $config->stack_name);
        Assert::same($version, $config->version);
        Assert::same($s3Prefix, $config->s3_prefix);
        Assert::same($confirmChangeset, $config->confirm_changeset);
        Assert::same($capabilities, $config->capabilities);
        Assert::same($parameterOverrides->parameters, $config->parameter_overrides->parameters);
        Assert::same($sourceBucketName, $config->source_bucket_name);
        Assert::same($cacheBucketName, $config->cache_bucket_name);

        Assert::same([
            'stack_name' => $stackName,
            'version' => $version,
            's3_bucket' => $s3Bucket,
            's3_prefix' => $s3Prefix,
            'region' => $region,
            'confirm_changeset' => $confirmChangeset,
            'capabilities' => $capabilities,
            'parameter_overrides' => $parameterOverrides->parameters,
            'source_bucket_name' => $sourceBucketName,
            'cache_bucket_name' => $cacheBucketName,
        ], $config->toArray());
    }
}

(new LambdaConfigTest())->run();
