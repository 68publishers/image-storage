<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;

interface CodecInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface $value
	 *
	 * @return string
	 */
	public function encode(ValueInterface $value): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface $value
	 *
	 * @return array
	 */
	public function decode(ValueInterface $value): array;
}
