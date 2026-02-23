<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class PresetCollection implements PresetCollectionInterface
{
    /** @var array<string, Preset> */
    private array $presets = [];

    public function add(string $presetAlias, Preset $preset): void
    {
        $this->presets[$presetAlias] = $preset;
    }

    public function has(string $presetAlias): bool
    {
        return isset($this->presets[$presetAlias]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $presetAlias): Preset
    {
        if (!$this->has($presetAlias)) {
            throw new InvalidArgumentException(sprintf(
                'Preset with the alias "%s" is not defined in the collection, please check your configuration.',
                $presetAlias,
            ));
        }

        return $this->presets[$presetAlias];
    }
}
