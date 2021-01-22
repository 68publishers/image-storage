<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Info;

use SixtyEightPublishers\ImageStorage\FileInfo;
use SixtyEightPublishers\ImageStorage\PathInfo;
use SixtyEightPublishers\FileStorage\Helper\Path;
use SixtyEightPublishers\ImageStorage\FileInfoInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

final class InfoFactory implements InfoFactoryInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface  */
	private $linkGenerator;

	/** @var string  */
	private $storageName;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 * @param \SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface     $linkGenerator
	 * @param string                                                                     $storageName
	 */
	public function __construct(ModifierFacadeInterface $modifierFacade, LinkGeneratorInterface $linkGenerator, string $storageName)
	{
		$this->modifierFacade = $modifierFacade;
		$this->linkGenerator = $linkGenerator;
		$this->storageName = $storageName;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function createPathInfo(string $path, $modifier = NULL): PathInfoInterface
	{
		$args = Path::parse($path);
		$args[] = $modifier;

		return new PathInfo($this->modifierFacade->getCodec(), ...$args);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createFileInfo(PathInfoInterface $pathInfo): FileInfoInterface
	{
		return new FileInfo($this->linkGenerator, $pathInfo, $this->storageName);
	}
}
