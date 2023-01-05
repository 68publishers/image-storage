<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Symfony\Console\Configurator;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfigurator;

require __DIR__ . '/../../../../bootstrap.php';

final class CleanCommandConfiguratorTest extends TestCase
{
	public function testOptionsShouldBeSet(): void
	{
		$command = Mockery::mock(Command::class);

		$command->shouldReceive('addOption')
			->once()
			->with(StorageCleaner::OPTION_CACHE_ONLY, null, InputOption::VALUE_NONE, 'Remove only cached files (image-storage only).');

		$configurator = new CleanCommandConfigurator();

		$configurator->setupOptions($command);
	}

	public function testEmptyCleanerOptionsShouldBeReturned(): void
	{
		$input = Mockery::mock(InputInterface::class);

		$input->shouldReceive('getOption')
			->once()
			->with(StorageCleaner::OPTION_CACHE_ONLY)
			->andReturn(false);

		$configurator = new CleanCommandConfigurator();
		$options = $configurator->getCleanerOptions($input);

		Assert::same([], $options);
	}

	public function testCleanerOptionsShouldBeReturned(): void
	{
		$input = Mockery::mock(InputInterface::class);

		$input->shouldReceive('getOption')
			->once()
			->with(StorageCleaner::OPTION_CACHE_ONLY)
			->andReturn(true);

		$configurator = new CleanCommandConfigurator();
		$options = $configurator->getCleanerOptions($input);

		Assert::same([
			StorageCleaner::OPTION_CACHE_ONLY => true,
		], $options);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}
}

(new CleanCommandConfiguratorTest())->run();
