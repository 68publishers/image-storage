<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;

final class PresetCodec implements CodecInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface  */
	private $codec;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface  */
	private $presetCollection;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface             $codec
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface $presetCollection
	 */
	public function __construct(CodecInterface $codec, PresetCollectionInterface $presetCollection)
	{
		$this->codec = $codec;
		$this->presetCollection = $presetCollection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function encode(ValueInterface $value): string
	{
		if ($value instanceof PresetValue) {
			$value = new Value($this->presetCollection->get($value->getPresetName()));
		}

		return $this->codec->encode($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decode(ValueInterface $value): array
	{
		if ($value instanceof PresetValue) {
			return $this->presetCollection->get($value->getPresetName());
		}

		return $this->codec->decode($value);
	}
}
