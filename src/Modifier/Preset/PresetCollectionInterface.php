<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface PresetCollectionInterface
{
    public function add(string $presetAlias, Preset $preset): void;

    public function has(string $presetAlias): bool;

    public function get(string $presetAlias): Preset;
}
