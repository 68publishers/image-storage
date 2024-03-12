<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use Stringable;

interface DescriptorInterface extends Stringable
{
    public function createSrcSet(ArgsFacade $args): string;
}
