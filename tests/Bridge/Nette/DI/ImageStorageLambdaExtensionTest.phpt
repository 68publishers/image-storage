<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\DI;

use Closure;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGenerator;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Command\DumpLambdaConfigCommand;
use Symfony\Component\Console\Application;
use Tester\Assert;
use Tester\CodeCoverage\Collector;
use Tester\TestCase;
use function assert;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.php';

final class ImageStorageLambdaExtensionTest extends TestCase
{
    public function testExceptionShouldBeThrownIfImageStorageExtensionNotRegistered(): void
    {
        Assert::exception(
            static fn () => ContainerFactory::create(__DIR__ . '/config/ImageStorageLambda/config.error.missingImageStorageExtension.neon'),
            RuntimeException::class,
            "The extension SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageLambdaExtension can be used only with SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension.",
        );
    }

    public function testExtensionShouldBeIntegrated(): void
    {
        $container = ContainerFactory::create(__DIR__ . '/config/ImageStorageLambda/config.neon');
        $application = $container->getByType(Application::class);
        $samConfigGenerator = $container->getByType(SamConfigGeneratorInterface::class);
        assert($application instanceof Application && $samConfigGenerator instanceof SamConfigGenerator);

        $command = $application->get('image-storage:lambda:dump-config');

        Assert::type(DumpLambdaConfigCommand::class, $command);

        call_user_func(Closure::bind(
            static function () use ($samConfigGenerator): void {
                Assert::same(__DIR__ . '/lambda', $samConfigGenerator->outputDir);
                Assert::same([
                    'images' => [
                        'stack_name' => null,
                        'version' => 1.0,
                        's3_bucket' => 'test_bucket',
                        's3_prefix' => null,
                        'region' => 'west',
                        'confirm_changeset' => false,
                        'capabilities' => 'CAPABILITY_IAM',
                        'parameter_overrides' => [],
                        'source_bucket_name' => null,
                        'cache_bucket_name' => null,
                    ],
                    'images2' => [
                        'stack_name' => 'test_stack',
                        'version' => 2.5,
                        's3_bucket' => 'test_bucket',
                        's3_prefix' => 'test_prefix',
                        'region' => 'west',
                        'confirm_changeset' => true,
                        'capabilities' => 'CAPABILITY_NAMED_IAM',
                        'parameter_overrides' => [
                            'TestKey' => 'TestValue',
                        ],
                        'source_bucket_name' => 'source',
                        'cache_bucket_name' => 'cache',
                    ],
                ], $samConfigGenerator->configs);
            },
            null,
            SamConfigGenerator::class,
        ));
    }

    protected function tearDown(): void
    {
        # save manually partial code coverage to free memory
        if (Collector::isStarted()) {
            Collector::save();
        }
    }
}

(new ImageStorageLambdaExtensionTest())->run();
