<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Command;

use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function is_string;
use function iterator_to_array;
use function sprintf;

#[AsCommand(name: 'image-storage:lambda:dump-config', description: 'Dumps AWS SAM configuration files for defined storages')]
final class DumpLambdaConfigCommand extends Command
{
    public function __construct(
        private readonly SamConfigGeneratorInterface $samConfigGenerator,
        private readonly FileStorageProviderInterface $fileStorageProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('storage', InputArgument::OPTIONAL, 'Generate config for specific storage only.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $storageName = $input->getArgument('storage');

        $storages = is_string($storageName)
            ? [$this->fileStorageProvider->get($storageName)]
            : array_filter(
                iterator_to_array($this->fileStorageProvider),
                fn (FileStorageInterface $fileStorage): bool => $fileStorage instanceof ImageStorageInterface && $this->samConfigGenerator->canGenerate($fileStorage),
            );

        foreach ($storages as $storage) {
            if (!$storage instanceof ImageStorageInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Storage "%s" is not an instance of %s.',
                    $storage->getName(),
                    ImageStorageInterface::class,
                ));
            }
        }

        foreach ($storages as $storage) {
            if (!$this->samConfigGenerator->canGenerate($storage)) {
                throw new InvalidArgumentException(sprintf(
                    'Lambda config for storage "%s" can not be generated.',
                    $storage->getName(),
                ));
            }

            $filename = $this->samConfigGenerator->generate($storage);

            $output->writeln(sprintf(
                'Successfully generated file %s',
                $filename,
            ));
        }

        return Command::SUCCESS;
    }
}
