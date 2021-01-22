<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Console\Configurator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use SixtyEightPublishers\FileStorage\Bridge\Console\Configurator\CleanCommandConfiguratorInterface;

final class CleanCommandConfiguration implements CleanCommandConfiguratorInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function setupOptions(Command $command): void
	{
		$command->addOption(StorageCleaner::OPTION_CACHE_ONLY, NULL, InputOption::VALUE_NONE, 'Remove only cached files (image-storage only).');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCleanerOptions(InputInterface $input): array
	{
		if ($input->hasOption(StorageCleaner::OPTION_CACHE_ONLY)) {
			return [
				StorageCleaner::OPTION_CACHE_ONLY => (bool) $input->getOption(StorageCleaner::OPTION_CACHE_ONLY),
			];
		}

		return [];
	}
}
