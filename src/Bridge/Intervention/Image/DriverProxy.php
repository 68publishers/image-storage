<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

use Intervention\Image\Image;
use Intervention\Image\AbstractColor;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Commands\AbstractCommand;

class DriverProxy extends AbstractDriver
{
	public function __construct(
		private readonly AbstractDriver $driver,
		private readonly CommandExecutorInterface $commandExecutor,
	) {
	}

	public function executeCommand($image, $name, $arguments): AbstractCommand
	{
		return $this->commandExecutor->execute($image, $name, $arguments);
	}

	public function newImage($width, $height, $background): Image
	{
		return $this->driver->newImage($width, $height, $background);
	}

	public function parseColor($value): AbstractColor
	{
		return $this->driver->parseColor($value);
	}

	protected function coreAvailable(): bool
	{
		return $this->driver->coreAvailable();
	}

	public function init($data): Image
	{
		return $this->driver->init($data);
	}

	public function encode($image, $format, $quality): Image
	{
		return $this->driver->encode($image, $format, $quality);
	}

	public function getDriverName(): string
	{
		return $this->driver->getDriverName();
	}
}
