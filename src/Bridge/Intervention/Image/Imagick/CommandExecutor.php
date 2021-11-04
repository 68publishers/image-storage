<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Imagick;
use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\AbstractCommandExecutor;

final class CommandExecutor extends AbstractCommandExecutor
{
	/**
	 * {@inheritDoc}
	 */
	protected function doExecute(Image $image, AbstractCommand $command): void
	{
		$core = $image->getCore();

		assert($core instanceof Imagick);

		if ('GIF' !== $core->getImageFormat()) {
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
