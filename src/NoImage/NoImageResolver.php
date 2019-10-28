<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\NoImage;

use Nette;
use SixtyEightPublishers;

final class NoImageResolver implements INoImageResolver
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider  */
	private $noImageProvider;

	/** @var array  */
	private $rules;

	/**
	 * Rules are in format [ no image name => regex ]
	 *
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider $noImageProvider
	 * @param array                                                       $rules
	 */
	public function __construct(INoImageProvider $noImageProvider, array $rules)
	{
		$this->noImageProvider = $noImageProvider;
		$this->rules = $rules;
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver ****************/

	/**
	 * {@inheritdoc}
	 */
	public function resolveNoImage(string $path): SixtyEightPublishers\ImageStorage\ImageInfo
	{
		foreach ($this->rules as $name => $regex) {
			if (preg_match('#' . $regex . '#', $path)) {
				return $this->noImageProvider->getNoImage($name);
			}
		}

		return $this->noImageProvider->getNoImage();
	}
}
