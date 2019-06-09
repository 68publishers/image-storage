<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Console;

use Nette;
use Symfony;

final class CopyAssetsCommand extends Symfony\Component\Console\Command\Command
{
	use Nette\SmartObject;

	/** @var array|\SixtyEightPublishers\ImageStorage\Assets\StorageAssets[]  */
	private $storageAssets;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Assets\StorageAssets[] $storageAssets
	 */
	public function __construct(array $storageAssets)
	{
		parent::__construct();

		$this->storageAssets = $storageAssets;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void
	{
		$this->setName('image-storage2:copy-assets')
			->setDescription('Copies assets from defined paths to configured storage')
			->addArgument('storage', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Storage name from configuration - you can specify storage via this argument', NULL);
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output): int
	{
		$storage = $input->getArgument('storage');

		try {
			foreach ($this->storageAssets as $storageAsset) {
				if (NULL !== $storage && $storage !== $storageAsset->getName()) {
					continue;
				}

				$storageAsset->persist();
				/** @var \SixtyEightPublishers\ImageStorage\Assets\Asset $asset */
				foreach ($storageAsset as $asset) {
					$output->writeln(sprintf(
						'Copied %s to %s://%s',
						$asset->getFileInfo()->getRealPath(),
						$storageAsset->getName(),
						$asset->getOutputPath()
					));
				}
			}
			$output->writeln("\nEnd");
		} catch (\Throwable $e) {
			$output->writeln(sprintf(
				'%s: %s',
				get_class($e),
				$e->getMessage()
			));

			return $e->getCode() === 0 ? 1 : (int) $e->getCode();
		}

		return 0;
	}
}
