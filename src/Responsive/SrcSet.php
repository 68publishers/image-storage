<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use Stringable;

final class SrcSet implements Stringable
{
    /**
     * @param "x"|"w"                $descriptor
     * @param array<numeric, string> $links
     */
    public function __construct(
        public readonly string $descriptor,
        public readonly array $links,
        public readonly string $value,
    ) {}

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
