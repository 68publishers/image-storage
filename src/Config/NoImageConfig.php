<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

final class NoImageConfig implements NoImageConfigInterface
{
    /**
     * @param array<string, string> $paths
     * @param array<string, string> $patterns
     */
    public function __construct(
        private readonly ?string $defaultPath,
        private readonly array $paths,
        private readonly array $patterns,
    ) {}

    public function getDefaultPath(): ?string
    {
        return $this->defaultPath;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }
}
