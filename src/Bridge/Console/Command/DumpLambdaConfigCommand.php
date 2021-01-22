<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixtyEightPublishers\FileStorage\FileStorageInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface;

final class DumpLambdaConfigCommand extends Command
{
	/** @var \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface  */
	private $samConfigGenerator;

	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\SamConfigGeneratorInterface $samConfigGenerator
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface                           $fileStorageProvider
	 */
	public function __construct(SamConfigGeneratorInterface $samConfigGenerator, FileStorageProviderInterface $fileStorageProvider)
	{
		parent::__construct();

		$this->samConfigGenerator = $samConfigGenerator;
		$this->fileStorageProvider = $fileStorageProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('image-storage:lambda:dump-config')
			->setDescription('Dumps AWS SAM configuration files for defined storages')
			->addArgument('storage', InputArgument::OPTIONAL, 'Generate config for specific storage only.', NULL);
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$storageName = $input->getArgument('storage');

		$storages = NULL !== $storageName ? [$this->fileStorageProvider->get($storageName)] : array_filter(iterator_to_array($this->fileStorageProvider), function (FileStorageInterface $fileStorage) {
			return $fileStorage instanceof ImageStorageInterface && $this->samConfigGenerator->hasStackForStorage($fileStorage);
		});

		foreach ($storages as $storage) {
			$filename = $this->samConfigGenerator->generateForStorage($storage);

			$output->writeln('Successfully generated file ' . $filename);
		}

		return 0;
	}
}
