<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick;

use Imagick;
use Intervention\Image\Image;
use Intervention\Image\Commands\AbstractCommand;
use SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\AbstractCommandExecutor;

final class CommandExecutor extends AbstractCommandExecutor
{
	private $decoder;

	/**
	 * @param string                                                                       $driverName
	 * @param \SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image\Imagick\Decoder $decoder
	 */
	public function __construct(string $driverName, Decoder $decoder)
	{
		parent::__construct($driverName);

		$this->decoder = $decoder;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doExecute(Image $image, AbstractCommand $command): void
	{
		$core = $image->getCore();

		assert($core instanceof Imagick);

		if ('GIF' !== $core->getImageFormat()) {
			$command->execute($this->decoder->initFromImagick($core));

			return;
		}

		$core = $core->coalesceImages();

		do {
			$command->execute($this->decoder->initFromImagick($core));
		} while ($core->nextImage());

		$core = $core->deconstructImages();

		$image->setCore($core);
	}
}
