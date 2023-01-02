<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use JsonException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;
use function md5;
use function json_encode;

final class RuntimeCachedCodec implements CodecInterface
{
	/** @var array<string, string>  */
	private array $encodeCache = [];

	/** @var array<string, array<string, string|numeric|bool>>  */
	private array $decodeCache = [];

	public function __construct(
		private readonly CodecInterface $codec,
	) {
	}

	/**
	 * @throws JsonException
	 */
	public function encode(ValueInterface $value): string
	{
		$key = $this->createCacheKey($value);

		return $this->encodeCache[$key] ?? ($this->encodeCache[$key] = $this->codec->encode($value));
	}

	/**
	 * @throws JsonException
	 */
	public function decode(ValueInterface $value): array
	{
		$key = $this->createCacheKey($value);

		return $this->decodeCache[$key] ?? ($this->decodeCache[$key] = $this->codec->decode($value));
	}

	/**
	 * @throws JsonException
	 */
	private function createCacheKey(ValueInterface $value): string
	{
		return md5(json_encode($value->getValue(), JSON_THROW_ON_ERROR));
	}
}
