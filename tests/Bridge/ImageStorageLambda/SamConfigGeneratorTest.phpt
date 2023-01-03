<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\ImageStorageLambda;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Aws\S3\S3ClientInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use SixtyEightPublishers\ImageStorage\Config\Config;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfig;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\Filesystem;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Filesystem\MountManager;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\LambdaConfig;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\ParameterOverrides;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGenerator;
use function uniqid;
use function file_exists;
use function sys_get_temp_dir;
use function file_get_contents;

require __DIR__ . '/../../bootstrap.php';

final class SamConfigGeneratorTest extends TestCase
{
	private string $outputDir;

	public function testCanGenerateMethodShouldReturnValidResults(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'a' => [],
		]);

		$imageStorageA = Mockery::mock(ImageStorageInterface::class);
		$imageStorageB = Mockery::mock(ImageStorageInterface::class);

		$imageStorageA->shouldReceive('getName')
			->withNoArgs()
			->andReturn('a');

		$imageStorageB->shouldReceive('getName')
			->withNoArgs()
			->andReturn('b');

		Assert::true($generator->canGenerate($imageStorageA));
		Assert::false($generator->canGenerate($imageStorageB));
	}

	public function testExceptionShouldBeThrownIfConfigIsNotDefined(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'a' => [],
		]);

		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->withNoArgs()
			->andReturn('b');

		Assert::exception(
			static fn () => $generator->generate($imageStorage),
			InvalidArgumentException::class,
			'Missing config with the name "b".'
		);
	}

	public function testExceptionShouldBeThrownIfFilesystemIsNotInstanceOfAdapterProviderInterface(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'a' => [
				's3_bucket' => 'test_bucket',
				'region' => 'west',
			],
		]);

		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->withNoArgs()
			->andReturn('a');

		$imageStorage->shouldReceive('getFilesystem')
			->once()
			->withNoArgs()
			->andReturn(Mockery::mock(FilesystemOperator::class));

		Assert::exception(
			static fn () => $generator->generate($imageStorage),
			InvalidStateException::class,
			'Can\'t detect bucket names from a filesystem because the filesystem must be an implementor of SixtyEightPublishers\ImageStorage\Filesystem\AdapterProviderInterface.'
		);
	}

	public function testExceptionShouldBeThrownIfFilesystemAdapterIsNotInstanceOfAwsS3V3Adapter(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'a' => [
				's3_bucket' => 'test_bucket',
				'region' => 'west',
			],
		]);

		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->withNoArgs()
			->andReturn('a');

		$imageStorage->shouldReceive('getFilesystem')
			->once()
			->withNoArgs()
			->andReturn($this->createFilesystemInMemoryMountManager());

		Assert::exception(
			static fn () => $generator->generate($imageStorage),
			InvalidStateException::class,
			'Adapter must be an instance of League\Flysystem\AwsS3V3\AwsS3V3Adapter.'
		);
	}

	public function testConfigShouldBeGeneratedWithMinimalConfiguration(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'minimal' => [
				's3_bucket' => 'test_bucket',
				'region' => 'west',
			],
		]);

		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->withNoArgs()
			->andReturn('minimal');

		$imageStorage->shouldReceive('getFilesystem')
			->once()
			->withNoArgs()
			->andReturn($this->createFilesystemS3MountManager());

		$imageStorage->shouldReceive('getConfig')
			->once()
			->withNoArgs()
			->andReturn(new Config([]));

		$imageStorage->shouldReceive('getNoImageConfig')
			->once()
			->withNoArgs()
			->andReturn(new NoImageConfig(null, [], []));

		$filename = $this->outputDir . '/minimal/samconfig.toml';

		try {
			$generator->generate($imageStorage);

			Assert::true(file_exists($filename));
			Assert::same(file_get_contents(__DIR__ . '/config.minimalConfiguration.toml'), file_get_contents($filename));
		} finally {
			@unlink($filename);
		}
	}

	public function testConfigShouldBeGeneratedWithFullConfiguration(): void
	{
		$generator = new SamConfigGenerator($this->outputDir, [
			'full' => [
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
			],
		]);

		$imageStorage = Mockery::mock(ImageStorageInterface::class);

		$imageStorage->shouldReceive('getName')
			->withNoArgs()
			->andReturn('full');

		$imageStorage->shouldReceive('getConfig')
			->once()
			->withNoArgs()
			->andReturn(new Config([
				ConfigInterface::BASE_PATH => 'images',
				Config::ENCODE_QUALITY => 80,
				Config::ALLOWED_PIXEL_DENSITY => [1.0, 2.0, 2.5, 3.0],
				Config::SIGNATURE_KEY => 'abc',
			]));

		$imageStorage->shouldReceive('getNoImageConfig')
			->once()
			->withNoArgs()
			->andReturn(new NoImageConfig(
				'noimage/noimage.png',
				[
					'test' => 'test/noimage.png',
				],
				[
					'test' => '^test\/',
				]
			));

		$filename = $this->outputDir . '/test_stack/samconfig.toml';

		try {
			$generator->generate($imageStorage);

			Assert::true(file_exists($filename));
			Assert::same(file_get_contents(__DIR__ . '/config.fullConfiguration.toml'), file_get_contents($filename));
		} finally {
			@unlink($filename);
		}
	}

	protected function setUp(): void
	{
		$this->outputDir = sys_get_temp_dir() . '/' . uniqid('68publishers:ImageStorage:SamConfigGeneratorTest', true);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function createFilesystemInMemoryMountManager(): MountManager
	{
		return new MountManager([
			ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => new Filesystem(
				new InMemoryFilesystemAdapter()
			),
			ImagePersisterInterface::FILESYSTEM_NAME_CACHE => new Filesystem(
				new InMemoryFilesystemAdapter()
			),
		]);
	}

	private function createFilesystemS3MountManager(): MountManager
	{
		return new MountManager([
			ImagePersisterInterface::FILESYSTEM_NAME_SOURCE => new Filesystem(
				new AwsS3V3Adapter(
					Mockery::mock(S3ClientInterface::class),
					'source_bucket'
				)
			),
			ImagePersisterInterface::FILESYSTEM_NAME_CACHE => new Filesystem(
				new AwsS3V3Adapter(
					Mockery::mock(S3ClientInterface::class),
					'cache_bucket'
				)
			),
		]);
	}
}

(new SamConfigGeneratorTest())->run();
