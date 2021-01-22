<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

final class TmpFileResource extends Resource
{
	/** @var \SixtyEightPublishers\ImageStorage\Resource\TmpFile  */
	private $tmpFile;

	/**
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface                        $pathInfo
	 * @param \Intervention\Image\Image                                                  $image
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Resource\TmpFile                        $tmpFile
	 */
	public function __construct(PathInfoInterface $pathInfo, Image $image, ModifierFacadeInterface $modifierFacade, TmpFile $tmpFile)
	{
		parent::__construct($pathInfo, $image, $modifierFacade);

		$this->tmpFile = $tmpFile;
	}

	/**
	 * {@inheritdoc}
	 */
	public function withPathInfo(PathInfoInterface $pathInfo): self
	{
		return new static($pathInfo, $this->getSource(), $this->modifierFacade, $this->tmpFile);
	}

	/**
	 * Destroy a tmp file
	 *
	 * @return void
	 */
	public function unlink(): void
	{
		$this->tmpFile->unlink();
	}
}
