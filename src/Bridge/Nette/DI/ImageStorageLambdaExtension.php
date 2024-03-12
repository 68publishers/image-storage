<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI;

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\LambdaConfig;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGenerator;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config\ImageStorageLambdaConfig;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Command\DumpLambdaConfigCommand;
use Yosymfony\Toml\TomlBuilder;
use function assert;

final class ImageStorageLambdaExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        $appDir = $this->getContainerBuilder()->parameters['appDir'] ?? '';

        return Expect::structure([
            'output_dir' => Expect::string($appDir . '/config/image-storage-lambda')->dynamic(),
            'stacks' => Expect::arrayOf(LambdaConfig::createSchema(), 'string'),
        ])->castTo(ImageStorageLambdaConfig::class);
    }

    public function loadConfiguration(): void
    {
        if (0 >= count($this->compiler->getExtensions(ImageStorageExtension::class))) {
            throw new RuntimeException(sprintf(
                'The extension %s can be used only with %s.',
                self::class,
                ImageStorageExtension::class,
            ));
        }

        if (!class_exists(TomlBuilder::class)) {
            throw new RuntimeException('Please require the package yosymfony/toml in your project.');
        }

        if (!class_exists(AwsS3V3Adapter::class)) {
            throw new RuntimeException('Please require the package league/flysystem-aws-s3-v3 in your project.');
        }

        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        assert($config instanceof ImageStorageLambdaConfig);

        $builder->addDefinition($this->prefix('sam_config_generator'))
            ->setType(SamConfigGeneratorInterface::class)
            ->setFactory(SamConfigGenerator::class, [
                $config->output_dir,
                array_map(static fn (LambdaConfig $lambdaConfig): array => $lambdaConfig->toArray(), $config->stacks),
            ]);

        $builder->addDefinition($this->prefix('command.dump_lambda_config'))
            ->setType(DumpLambdaConfigCommand::class);
    }
}
