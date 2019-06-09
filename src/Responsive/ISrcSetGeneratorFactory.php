<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use SixtyEightPublishers;

interface ISrcSetGeneratorFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator    $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator
	 */
	public function create(SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator, SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade): SrcSetGenerator;
}
