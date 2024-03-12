<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Imagick;
use ImagickException;
use Intervention\Image\Commands\AbstractCommand;
use Intervention\Image\Image;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\AbstractCommandExecutor;

final class CommandExecutor extends AbstractCommandExecutor
{
    private const ANIMATED_FORMATS = [
        'gif',
        'webp',
    ];

    /**
     * @throws ImagickException
     */
    protected function doExecute(Image $image, AbstractCommand $command): void
    {
        $core = $image->getCore();

        assert($core instanceof Imagick);

        if (!in_array(strtolower($core->getImageFormat()), self::ANIMATED_FORMATS, true)) {
            $command->execute($image);

            return;
        }

        $core = $core->coalesceImages();

        $image->setCore($core);

        do {
            $command->execute($image);
        } while ($core->nextImage());

        $image->setCore($core->deconstructImages());
    }
}
