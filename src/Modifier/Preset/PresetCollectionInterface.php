<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface PresetCollectionInterface
{
    /**
     * @param array<string, string|numeric|bool> $parameters
     */
    public function add(string $presetAlias, array $parameters): void;

    public function has(string $presetAlias): bool;

    /**
     * @return array<string, string|numeric|bool>
     */
    public function get(string $presetAlias): array;
}
