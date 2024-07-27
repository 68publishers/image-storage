<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use Stringable;

interface DescriptorInterface extends Stringable
{
    public function createSrcSet(ArgsFacade $args): SrcSet;
}
