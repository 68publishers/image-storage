<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Intervention\Image\Image;

final class ModifyResult
{
    public function __construct(
        public readonly Image $image,
        public readonly bool $modified,
        public readonly ?string $encodeFormat,
        public readonly ?int $encodeQuality,
    ) {}
}
