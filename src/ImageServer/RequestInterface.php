<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

interface RequestInterface
{
    public function getUrlPath(): string;

    /**
     * @return array<int|string, mixed>|string|null
     */
    public function getQueryParameter(string $name): array|string|null;

    public function getOriginalRequest(): object;
}
