<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Configurator;

use SixtyEightPublishers\FileStorage\Bridge\Symfony\Console\Configurator\CleanCommandConfiguratorInterface;
use SixtyEightPublishers\ImageStorage\Cleaner\StorageCleaner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class CleanCommandConfigurator implements CleanCommandConfiguratorInterface
{
    public function setupOptions(Command $command): void
    {
        $command->addOption(StorageCleaner::OPTION_CACHE_ONLY, null, InputOption::VALUE_NONE, 'Remove only cached files (image-storage only).');
    }

    public function getCleanerOptions(InputInterface $input): array
    {
        $cacheOnly = $input->getOption(StorageCleaner::OPTION_CACHE_ONLY);

        if ($cacheOnly) {
            return [
                StorageCleaner::OPTION_CACHE_ONLY => true,
            ];
        }

        return [];
    }
}
