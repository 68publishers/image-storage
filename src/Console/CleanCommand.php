<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Console;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class CleanCommand extends Symfony\Component\Console\Command\Command
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner[]  */
	private $cleaners = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner[] $cleaners
	 */
	public function __construct(array $cleaners)
	{
		parent::__construct();

		foreach ($cleaners as $cleaner) {
			$this->addCleaner($cleaner);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('image-storage:clean')
			->setDescription('Cleans storage')
			->addArgument('storage', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Storage name from configuration - you can specify storage via this argument', NULL)
			->addOption('namespace', NULL, Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Search only in defined namespace', NULL)
			->addOption('cache-only', NULL, Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Remove only cached/modified images (save originals)?');
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output): int
	{
		$storage = $input->getArgument('storage');
		$namespace = (string) $input->getOption('namespace');
		$cacheOnly = (bool) $input->getOption('cache-only');

		try {
			if (!empty($storage)) {
				$cleaner = $this->getCleaner($storage);

				if ($this->ask($input, $output, $cleaner->getCount($namespace, $cacheOnly), $storage, $namespace)) {
					$cleaner->clean($namespace, $cacheOnly);
					$output->writeln(sprintf('Storage %s was successfully cleaned.', $storage));
				}
			} else {
				$count = array_sum(array_map(function (SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner $cleaner) use ($namespace, $cacheOnly) {
					return $cleaner->getCount($namespace, $cacheOnly);
				}, $this->cleaners));

				if ($this->ask($input, $output, $count, 'all', $namespace)) {
					foreach ($this->cleaners as $name => $cleaner) {
						$cleaner->clean($namespace, $cacheOnly);
						$output->writeln(sprintf('Storage %s was successfully cleaned.', $name));
					}
				}
			}

			$output->writeln("\nEnd");
		} catch (\Throwable $e) {
			$output->writeln(sprintf(
				'%s: %s',
				get_class($e),
				$e->getMessage()
			));

			return $e->getCode() === 0 ? 1 : $e->getCode();
		}

		return 0;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @param int                                               $count
	 * @param string                                            $storageName
	 * @param string                                            $prefix
	 *
	 * @return bool
	 */
	private function ask(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output, int $count, string $storageName, string $prefix): bool
	{
		return (bool) $this->getHelper('question')->ask($input, $output, new Symfony\Component\Console\Question\ConfirmationQuestion(sprintf(
			'Do you want to delete %d images %sin %s storage? ',
			$count,
			$prefix === '' ? '' : sprintf('with prefix "%s" ', $prefix),
			$storageName
		), FALSE));
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner $cleaner
	 *
	 * @return void
	 */
	private function addCleaner(SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner $cleaner): void
	{
		$this->cleaners[$cleaner->getName()] = $cleaner;
	}

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner
	 */
	private function getCleaner(string $name): SixtyEightPublishers\ImageStorage\Cleaner\IStorageCleaner
	{
		if (!array_key_exists($name, $this->cleaners)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Missing storage cleaner with name %s',
				$name
			));
		}

		return $this->cleaners[$name];
	}
}
