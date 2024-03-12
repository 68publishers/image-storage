<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;

interface CodecInterface
{
    public function encode(ValueInterface $value): string;

    /**
     * @return array<string, string|numeric|bool>
     */
    public function decode(ValueInterface $value): array;
}
