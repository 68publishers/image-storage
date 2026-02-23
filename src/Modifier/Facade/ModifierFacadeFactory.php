<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Codec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\PresetCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\RuntimeCachedCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface;

final class ModifierFacadeFactory implements ModifierFacadeFactoryInterface
{
    public function __construct(
        private readonly PresetCollectionFactoryInterface $presetCollectionFactory,
        private readonly ModifierCollectionFactoryInterface $modifierCollectionFactory,
    ) {}

    public function create(ConfigInterface $config): ModifierFacadeInterface
    {
        $presetCollection = $this->presetCollectionFactory->create();
        $modifierCollection = $this->modifierCollectionFactory->create();

        $codec = new RuntimeCachedCodec(
            codec: new PresetCodec(
                codec: new Codec(
                    config: $config,
                    modifierCollection: $modifierCollection,
                ),
                config: $config,
                modifierCollection: $modifierCollection,
                presetCollection: $presetCollection,
            ),
        );

        return new ModifierFacade(
            config: $config,
            codec: $codec,
            presetCollection: $presetCollection,
            modifierCollection: $modifierCollection,
        );
    }
}
