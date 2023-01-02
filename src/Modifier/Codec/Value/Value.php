<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec\Value;

final class Value implements ValueInterface
{
	/**
	 * @param string|array<string, string|numeric|bool> $value
	 */
	public function __construct(
		private readonly array|string $value,
	) {
	}

	/**
	 * @return string|array<string, string|numeric|bool>
	 */
	public function getValue(): array|string
	{
		return $this->value;
	}
}
