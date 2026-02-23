<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use function array_merge;
use function explode;
use function is_string;
use function str_contains;

final class PresetCodec implements CodecInterface
{
    public function __construct(
        private readonly CodecInterface $codec,
        private readonly ConfigInterface $config,
        private readonly ModifierCollectionInterface $modifierCollection,
        private readonly PresetCollectionInterface $presetCollection,
    ) {}

    public function modifiersToPath(string|array $value): string
    {
        if (is_string($value)) {
            $value = $this->doExpand(value: $value);
        }

        return $this->codec->modifiersToPath(value: $value);
    }

    public function pathToModifiers(string $value): array
    {
        return $this->codec->pathToModifiers(value: $value);
    }

    public function expandModifiers(array|string $value): array
    {
        if (is_string($value)) {
            $value = $this->doExpand(value: $value);
        }

        return $this->codec->expandModifiers(value: $value);
    }

    /**
     * @return array<string, string|numeric|bool>
     */
    private function doExpand(string $value): array
    {
        $presetAlias = $value;
        $presetValue = true;
        $assigner = $this->config[Config::MODIFIER_ASSIGNER];
        $assigner = empty($assigner) ? ':' : $assigner;

        if (str_contains($presetAlias, $assigner)) {
            [$presetAlias, $presetValue] = explode($assigner, $presetAlias, 2);
        }

        $preset = $this->presetCollection->get(presetAlias: $presetAlias);
        $modifiers[] = $preset->modifiers;

        if (null !== $preset->descriptor) {
            $presetValue = $preset->descriptor->validateModifierValue(
                value: $presetValue,
                default: $preset->defaultDescriptorValue,
            );

            $modifiers[] = $preset->descriptor->expandModifier(
                modifierCollection: $this->modifierCollection,
                value: $presetValue,
            );
        }

        return array_merge(...$modifiers);
    }
}
