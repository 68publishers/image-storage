<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

interface SrcSetGeneratorFactoryInterface
{
	public function create(LinkGeneratorInterface $linkGenerator, ModifierFacadeInterface $modifierFacade): SrcSetGenerator;
}
