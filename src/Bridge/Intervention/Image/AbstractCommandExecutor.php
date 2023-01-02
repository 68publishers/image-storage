<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Exception\NotSupportedException;
use function substr;
use function sprintf;
use function ucfirst;
use function in_array;
use function mb_substr;
use function strtoupper;
use function class_exists;
use function mb_strtoupper;
use function extension_loaded;

abstract class AbstractCommandExecutor implements CommandExecutorInterface
{
	public function __construct(
		private readonly string $driverName,
	) {
	}

	abstract protected function doExecute(Image $image, AbstractCommand $command): void;

	public function execute(Image $image, string $name, array $arguments): AbstractCommand
	{
		$commandClassName = $this->getCommandClassName($name);
		$command = $this->createCommand($commandClassName, $arguments);

		if (!in_array(ucfirst($name), MultiFrameCommandList::LIST, true)) {
			$command->execute($image);

			return $command;
		}

		$this->doExecute($image, $command);

		return $command;
	}

	/**
	 * @return class-string
	 */
	protected function getCommandClassName(string $name): string
	{
		if (extension_loaded('mbstring')) {
			$name = mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
		} else {
			$name = strtoupper(substr($name, 0, 1)) . substr($name, 1);
		}

		$classnameLocal = sprintf('\Intervention\Image\%s\Commands\%sCommand', $this->driverName, ucfirst($name));
		$classnameGlobal = sprintf('\Intervention\Image\Commands\%sCommand', ucfirst($name));

		if (class_exists($classnameLocal)) {
			return $classnameLocal;
		}

		if (class_exists($classnameGlobal)) {
			return $classnameGlobal;
		}

		throw new NotSupportedException(sprintf(
			'Command (%s) is not available for driver (%s).',
			$name,
			$this->driverName
		));
	}

	/**
	 * @param class-string      $className
	 * @param array<int, mixed> $arguments
	 */
	protected function createCommand(string $className, array $arguments): AbstractCommand
	{
		return new $className($arguments);
	}
}
