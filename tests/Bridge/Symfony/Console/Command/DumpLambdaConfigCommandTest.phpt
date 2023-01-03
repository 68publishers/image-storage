<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Symfony\Console\Command;

use Mockery;
use ArrayIterator;
use Tester\Assert;
use Tester\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Command\DumpLambdaConfigCommand;
use function assert;

require __DIR__ . '/../../../../bootstrap.php';

final class DumpLambdaConfigCommandTest extends TestCase
{
	public function testConfigsForAllImageStoragesShouldBeDumped(): void
	{
		$provider = Mockery::mock(FileStorageProviderInterface::class);
		$storage1 = Mockery::mock(FileStorageInterface::class);
		$storage2 = Mockery::mock(ImageStorageInterface::class);
		$storage3 = Mockery::mock(ImageStorageInterface::class);
		$storage4 = Mockery::mock(ImageStorageInterface::class);
		$samConfigGenerator = Mockery::mock(SamConfigGeneratorInterface::class);

		$provider->shouldReceive('getIterator')
			->once()
			->andReturn(new ArrayIterator([
				'default' => $storage1,
				'a' => $storage2,
				'b' => $storage3,
				'c' => $storage4,
			]));

		$samConfigGenerator->shouldReceive('canGenerate')
			->times(2)
			->with($storage2)
			->andReturn(true);

		$samConfigGenerator->shouldReceive('canGenerate')
			->times(2)
			->with($storage3)
			->andReturn(true);

		$samConfigGenerator->shouldReceive('canGenerate')
			->once()
			->with($storage4)
			->andReturn(false);

		$samConfigGenerator->shouldReceive('generate')
			->once()
			->with($storage2)
			->andReturn('/config/a.toml');

		$samConfigGenerator->shouldReceive('generate')
			->once()
			->with($storage3)
			->andReturn('/config/b.toml');

		$tester = $this->createCommandTester($samConfigGenerator, $provider);

		$tester->execute([]);

		$display = $tester->getDisplay();

		Assert::same(0, $tester->getStatusCode());
		Assert::contains('Successfully generated file /config/a.toml', $display);
		Assert::contains('Successfully generated file /config/b.toml', $display);
	}

	public function testConfigForSpecifiedImageStoragesShouldBeDumped(): void
	{
		$provider = Mockery::mock(FileStorageProviderInterface::class);
		$storage = Mockery::mock(ImageStorageInterface::class);
		$samConfigGenerator = Mockery::mock(SamConfigGeneratorInterface::class);

		$provider->shouldReceive('get')
			->once()
			->with('storage_a')
			->andReturn($storage);

		$samConfigGenerator->shouldReceive('canGenerate')
			->once()
			->with($storage)
			->andReturn(true);

		$samConfigGenerator->shouldReceive('generate')
			->once()
			->with($storage)
			->andReturn('/config/storage_a.toml');

		$tester = $this->createCommandTester($samConfigGenerator, $provider);

		$tester->execute([
			'storage' => 'storage_a',
		]);

		$display = $tester->getDisplay();

		Assert::same(0, $tester->getStatusCode());
		Assert::contains('Successfully generated file /config/storage_a.toml', $display);
	}

	public function testExceptionShouldBeThrownIfSpecifiedStorageIsNotInstanceOfImageStorage(): void
	{
		$provider = Mockery::mock(FileStorageProviderInterface::class);
		$storage = Mockery::mock(FileStorageInterface::class);
		$samConfigGenerator = Mockery::mock(SamConfigGeneratorInterface::class);

		$provider->shouldReceive('get')
			->once()
			->with('storage_a')
			->andReturn($storage);

		$storage->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('storage_a');

		$tester = $this->createCommandTester($samConfigGenerator, $provider);

		Assert::exception(
			static fn () => $tester->execute([
				'storage' => 'storage_a',
			]),
			InvalidArgumentException::class,
			'Storage "storage_a" is not an instance of SixtyEightPublishers\ImageStorage\ImageStorageInterface.'
		);
	}

	public function testExceptionShouldBeThrownIfGeneratorCanNotGenerateConfigForSpecifiedStorage(): void
	{
		$provider = Mockery::mock(FileStorageProviderInterface::class);
		$storage = Mockery::mock(ImageStorageInterface::class);
		$samConfigGenerator = Mockery::mock(SamConfigGeneratorInterface::class);

		$provider->shouldReceive('get')
			->once()
			->with('storage_a')
			->andReturn($storage);

		$samConfigGenerator->shouldReceive('canGenerate')
			->once()
			->with($storage)
			->andReturn(false);

		$storage->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('storage_a');

		$tester = $this->createCommandTester($samConfigGenerator, $provider);

		Assert::exception(
			static fn () => $tester->execute([
				'storage' => 'storage_a',
			]),
			InvalidArgumentException::class,
			'Lambda config for storage "storage_a" can not be generated.'
		);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function createCommandTester(SamConfigGeneratorInterface $samConfigGenerator, FileStorageProviderInterface $fileStorageProvider): CommandTester
	{
		$command = new DumpLambdaConfigCommand($samConfigGenerator, $fileStorageProvider);
		$application = new Application();

		$application->add($command);

		$command = $application->find('image-storage:lambda:dump-config');
		assert($command instanceof DumpLambdaConfigCommand);

		return new CommandTester($command);
	}
}

(new DumpLambdaConfigCommandTest())->run();
