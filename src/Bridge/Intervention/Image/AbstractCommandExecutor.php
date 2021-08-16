<?php


namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Exception\NotSupportedException;

abstract class AbstractCommandExecutor implements CommandExecutorInterface
{
	private $driverName;

	/**
	 * @param string $driverName
	 */
	public function __construct(string $driverName)
	{
		$this->driverName = $driverName;
	}

	/**
	 * @param \Intervention\Image\Image $image
	 * @param \Intervention\Image\Commands\AbstractCommand $command
	 *
	 * @return void
	 */
	abstract protected function doExecute(Image $image, AbstractCommand $command) : void;

	/**
	 * {@inheritDoc}
	 */
	public function execute(Image $image, string $name, array $arguments): AbstractCommand
	{
		$commandClassName = $this->getCommandClassName($name);
		$command = $this->createCommand($commandClassName, $arguments);

		if (!in_array(ucfirst($name), MultiFrameCommandList::LIST, TRUE)) {
			$command->execute($image);

			return $command;
		}

		$this->doExecute($image, $command);

		return $command;
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	protected function getCommandClassName(string $name) : string
	{
		$name = mb_convert_case($name[0], MB_CASE_UPPER, 'utf-8') . mb_substr($name, 1, mb_strlen($name));

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
	 * @param string $className
	 * @param array $arguments
	 *
	 * @return \Intervention\Image\Commands\AbstractCommand
	 */
	protected function createCommand(string $className, array $arguments) : AbstractCommand
	{
		return new $className($arguments);
	}
}
