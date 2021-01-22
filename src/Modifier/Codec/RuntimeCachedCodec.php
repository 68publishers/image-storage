<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;

final class RuntimeCachedCodec implements CodecInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface  */
	private $codec;

	/** @var array  */
	private $encodeCache = [];

	/** @var array  */
	private $decodeCache = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface $codec
	 */
	public function __construct(CodecInterface $codec)
	{
		$this->codec = $codec;
	}

	/**
	 * {@inheritDoc}
	 */
	public function encode(ValueInterface $value): string
	{
		$key = $this->createCacheKey($value);

		if (isset($this->encodeCache[$key])) {
			return $this->encodeCache[$key];
		}

		return $this->encodeCache[$key] = $this->codec->encode($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decode(ValueInterface $value): array
	{
		$key = $this->createCacheKey($value);

		if (isset($this->decodeCache[$key])) {
			return $this->decodeCache[$key];
		}

		return $this->decodeCache[$key] = $this->codec->decode($value);
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface $value
	 *
	 * @return string
	 */
	private function createCacheKey(ValueInterface $value): string
	{
		$val = $value->getValue();

		if (is_array($val)) {
			return json_encode($val, JSON_THROW_ON_ERROR);
		}

		return 'str_' . $val;
	}
}
