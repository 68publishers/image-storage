<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Symfony\Console\Command;

use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function assert;
use function is_string;
use function iterator_to_array;
use function sprintf;

final class DumpLambdaConfigCommand extends Command
{
    protected static $defaultName = 'image-storage:lambda:dump-config';

    public function __construct(
        private readonly SamConfigGeneratorInterface $samConfigGenerator,
        private readonly FileStorageProviderInterface $fileStorageProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Dumps AWS SAM configuration files for defined storages')
            ->addArgument('storage', InputArgument::OPTIONAL, 'Generate config for specific storage only.');
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
            assert($storage instanceof FileStorageInterface);

            if (!$storage instanceof ImageStorageInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Storage "%s" is not an instance of %s.',
                    $storage->getName(),
                    ImageStorageInterface::class,
                ));
            }
        }

        foreach ($storages as $storage) {
            assert($storage instanceof ImageStorageInterface);

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
