<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Console;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class DumpLambdaConfigCommand extends Symfony\Component\Console\Command\Command
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/** @var \SixtyEightPublishers\ImageStorage\SamConfig\IImageStorageConfigGenerator  */
	private $imageStorageConfigGenerator;

	/** @var string  */
	private $outputDir;

	/** @var array  */
	private $stacks = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider                  $imageStorageProvider
	 * @param \SixtyEightPublishers\ImageStorage\SamConfig\IImageStorageConfigGenerator $imageStorageConfigGenerator
	 * @param string                                                                    $outputDir
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider, SixtyEightPublishers\ImageStorage\SamConfig\IImageStorageConfigGenerator $imageStorageConfigGenerator, string $outputDir)
	{
		parent::__construct();

		$this->imageStorageProvider = $imageStorageProvider;
		$this->imageStorageConfigGenerator = $imageStorageConfigGenerator;
		$this->outputDir = $outputDir;
	}

	/**
	 * @param string $name
	 * @param array  $values
	 *
	 * @return void
	 */
	public function addStack(string $name, array $values): void
	{
		$this->stacks[$name] = $values;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('image-storage:lambda:dump-config')
			->setDescription('Dumps AWS SAM configuration files for defined storages')
			->addArgument('storage', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'A storage name from a configuration - you can specify the storage via this argument', NULL);
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output): int
	{
		$storage = $input->getArgument('storage');
		$stacks = $this->stacks;

		if (NULL !== $storage) {
			if (!isset($stacks[$storage])) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'Stack for a storage "%s" is not defined.',
					$storage
				));
			}

			$this->stacks = [
				$this->stacks[$storage],
			];
		}

		foreach ($stacks as $name => $stack) {
			$outputPath = sprintf(
				'%s/%s/samconfig.toml',
				rtrim($this->outputDir, '/'),
				$stack['stack_name']
			);

			$output->writeln('Generating file ' . $outputPath);
			$this->imageStorageConfigGenerator->generate($this->imageStorageProvider->get($name), $stack, $outputPath);
			$output->write(' ... done');
		}

		return 0;
	}
}
