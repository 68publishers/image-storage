<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

class Resource implements ResourceInterface
{
	/** @var \SixtyEightPublishers\FileStorage\PathInfoInterface  */
	private $pathInfo;

	/** @var \Intervention\Image\Image  */
	private $image;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	protected $modifierFacade;

	/**
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface                        $pathInfo
	 * @param \Intervention\Image\Image                                                  $image
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 */
	public function __construct(PathInfoInterface $pathInfo, Image $image, ModifierFacadeInterface $modifierFacade)
	{
		$this->pathInfo = $pathInfo;
		$this->image = $image;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPathInfo(): PathInfoInterface
	{
		return $this->pathInfo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSource(): Image
	{
		return $this->image;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPathInfo(PathInfoInterface $pathInfo)
	{
		return new static($pathInfo, $this->image, $this->modifierFacade);
	}

	/**
	 * {@inheritdoc}
	 */
	public function modifyImage($modifiers): void
	{
		$this->image = $this->modifierFacade->modifyImage($this->image, $this->pathInfo, $modifiers);
	}
}
