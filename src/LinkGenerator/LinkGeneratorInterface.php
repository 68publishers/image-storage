<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface as BaseLinkGeneratorInterface;

interface LinkGeneratorInterface extends BaseLinkGeneratorInterface
{
	public function srcSet(PathInfoInterface $info, DescriptorInterface $descriptor): string;

	public function getSignatureStrategy(): ?SignatureStrategyInterface;
}
