<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Nette;
use Intervention;
use SixtyEightPublishers;

class Resource implements IResource
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\ImageInfo  */
	private $info;

	/** @var \Intervention\Image\Image  */
	private $image;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \Intervention\Image\Image                                          $image
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                       $info
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		Intervention\Image\Image $image,
		SixtyEightPublishers\ImageStorage\ImageInfo $info,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->info = $info;
		$this->image = $image;
		$this->modifierFacade = $modifierFacade;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Resource\IResource **************/

	/**
	 * {@inheritdoc}
	 */
	public function getInfo(): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		return $this->info;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getImage(): Intervention\Image\Image
	{
		return $this->image;
	}

	/**
	 * {@inheritdoc}
	 */
	public function modifyImage($modifier): void
	{
		$this->image = $this->modifierFacade->modifyImage($this->image, $this->info, $modifier);
	}
}
