<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface as BaseLinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;

interface LinkGeneratorInterface extends BaseLinkGeneratorInterface
{
    public function srcSet(PathInfoInterface $info, DescriptorInterface $descriptor): SrcSet;

    public function getSignatureStrategy(): ?SignatureStrategyInterface;
}
