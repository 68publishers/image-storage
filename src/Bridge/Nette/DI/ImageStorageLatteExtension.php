<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI;

use Latte\Engine;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\PhpLiteral;
use Nette\DI\Definitions\FactoryDefinition;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Bridge\Latte\ImageStorageFunctions;

final class ImageStorageLatteExtension extends CompilerExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		$functionNames = [];

		foreach (ImageStorageFunctions::DEFAULT_FUNCTION_NAMES as $functionId => $defaultFunctionName) {
			$functionNames[$functionId] = Expect::string($defaultFunctionName);
		}

		return Expect::structure([
			'function_names' => Expect::structure($functionNames),
			#   create_w_descriptor: w_descriptor
			#   create_x_descriptor: x_descriptor
			#   create_w_descriptor_from_range: w_descriptor_range
			#   create_no_image: no_image
		]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\RuntimeException
	 */
	public function loadConfiguration(): void
	{
		if (0 >= count($this->compiler->getExtensions(ImageStorageExtension::class))) {
			throw new RuntimeException(sprintf(
				'The extension %s can be used only with %s.',
				static::class,
				ImageStorageExtension::class
			));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$latteFactory = $builder->getDefinition($builder->getByType(Engine::class) ?? 'nette.latteFactory');

		if ($latteFactory instanceof FactoryDefinition) {
			$latteFactory = $latteFactory->getResultDefinition();
		}

		$latteFactory->addSetup('?::register(?, ?, ?)', [
			new PhpLiteral(ImageStorageFunctions::class),
			'@' . FileStorageProviderInterface::class,
			'@self',
			(array) $this->config->function_names,
		]);
	}
}
