<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

interface SrcSetGeneratorFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface     $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator
	 */
	public function create(LinkGeneratorInterface $linkGenerator, ModifierFacadeInterface $modifierFacade): SrcSetGenerator;
}
