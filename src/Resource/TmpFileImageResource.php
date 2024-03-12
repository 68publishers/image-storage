<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

final class TmpFileImageResource extends ImageResource
{
	public function __construct(
		PathInfoInterface $pathInfo,
		Image $image,
		ModifierFacadeInterface $modifierFacade,
		private readonly TmpFile $tmpFile,
	) {
		parent::__construct(
            pathInfo: $pathInfo,
            image: $image,
            modifierFacade: $modifierFacade,
        );
	}

	/**
	 * Destroy a tmp file
	 */
	public function unlink(): void
	{
		$this->tmpFile->unlink();
	}
}
