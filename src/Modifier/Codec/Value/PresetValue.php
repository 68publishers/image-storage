<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec\Value;

final class PresetValue implements ValueInterface
{
	public function __construct(
		public readonly string $presetName,
	) {
	}

	public function getValue(): string
	{
		return $this->presetName;
	}
}
