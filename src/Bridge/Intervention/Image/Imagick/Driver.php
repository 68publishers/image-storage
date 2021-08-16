<?php

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Intervention\Image\Imagick\Driver as ImagickDriver;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\DriverProxy;

final class Driver extends DriverProxy
{
	public function __construct()
	{
		$decoder = new Decoder();
		$driver = new ImagickDriver($decoder);
		$executor = new CommandExecutor($driver->getDriverName(), $decoder);

		parent::__construct($driver, $executor);
	}
}
