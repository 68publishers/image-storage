<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;

final class PresetCodec implements CodecInterface
{
    public function __construct(
        private readonly CodecInterface $codec,
        private readonly PresetCollectionInterface $presetCollection,
    ) {}

    public function encode(ValueInterface $value): string
    {
        if ($value instanceof PresetValue) {
            $value = new Value($this->presetCollection->get($value->presetName));
        }

        return $this->codec->encode($value);
    }

    public function decode(ValueInterface $value): array
    {
        if ($value instanceof PresetValue) {
            return $this->presetCollection->get($value->presetName);
        }

        return $this->codec->decode($value);
    }
}
