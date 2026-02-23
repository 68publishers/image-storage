<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

final class KnownModifiers
{
    /**
     * @param array<string, true> $list
     */
    public function __construct(
        public readonly array $list,
    ) {}

    public function isKnown(string $modifiers): bool
    {
        return isset($this->list[$modifiers]);
    }
}
