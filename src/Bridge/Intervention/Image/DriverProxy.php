<?php

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\AbstractColor;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Commands\AbstractCommand;

class DriverProxy extends AbstractDriver
{
	private $driver;

	private $commandExecutor;

	/**
	 * @param \Intervention\Image\AbstractDriver $driver
	 * @param \SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\CommandExecutorInterface $commandExecutor
	 */
	public function __construct(AbstractDriver $driver, CommandExecutorInterface $commandExecutor)
	{
		$this->driver = $driver;
		$this->commandExecutor = $commandExecutor;
	}

	/**
	 * {@inheritDoc}
	 */
	public function executeCommand($image, $name, $arguments) : AbstractCommand
	{
		return $this->commandExecutor->execute($image, $name, $arguments);
	}

	/**
	 * {@inheritDoc}
	 */
	public function newImage($width, $height, $background) : Image
	{
		return $this->driver->newImage($width, $height, $background);
	}

	/**
	 * {@inheritDoc}
	 */
	public function parseColor($value) : AbstractColor
	{
		return $this->driver->parseColor($value);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function coreAvailable() : bool
	{
		return $this->driver->coreAvailable();
	}

	/**
	 * {@inheritDoc}
	 */
	public function init($data) : Image
	{
		return $this->driver->init($data);
	}

	/**
	 * @param \Intervention\Image\Image $image
	 * @param string $format
	 * @param int $quality
	 *
	 * @return \Intervention\Image\Image
	 */
	public function encode($image, $format, $quality) : Image
	{
		return $this->driver->encode($image, $format, $quality);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDriverName() : string
	{
		return $this->driver->getDriverName();
	}
}
