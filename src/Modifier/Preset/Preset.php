<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;

final class Preset
{
    /**
     * @param array<string, string|numeric|bool> $modifiers
     */
    public function __construct(
        public readonly array $modifiers,
        public readonly ?DescriptorInterface $descriptor,
        public readonly mixed $defaultDescriptorValue,
    ) {}
}
