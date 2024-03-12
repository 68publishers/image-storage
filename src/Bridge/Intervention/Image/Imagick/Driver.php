<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Intervention\Image\Imagick\Driver as ImagickDriver;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\DriverProxy;

final class Driver extends DriverProxy
{
    public function __construct()
    {
        $driver = new ImagickDriver(new Decoder(), new Encoder());
        $executor = new CommandExecutor($driver->getDriverName());

        parent::__construct($driver, $executor);
    }
}
