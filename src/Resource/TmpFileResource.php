<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention;
use SixtyEightPublishers;

final class TmpFileResource extends Resource
{
	/** @var string  */
	private $tmpFilename;

	/** @var bool  */
	private $unlinked = FALSE;

	/**
	 * @param \Intervention\Image\Image                                          $image
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                       $info
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 * @param string                                                             $tmpFilename
	 */
	public function __construct(
		Intervention\Image\Image $image,
		SixtyEightPublishers\ImageStorage\ImageInfo $info,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		string $tmpFilename
	) {
		parent::__construct($image, $info, $modifierFacade);

		$this->tmpFilename = $tmpFilename;
	}

	/**
	 * Destroy tmp file
	 *
	 * @return void
	 */
	public function unlink(): void
	{
		if (FALSE === $this->unlinked) {
			@unlink($this->tmpFilename);

			$this->unlinked = TRUE;
		}
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		$this->unlink();
	}
}
