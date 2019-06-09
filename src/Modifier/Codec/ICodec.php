<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

interface ICodec
{
	/**
	 * Convert array to string and validate existence of parameters
	 *
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function encode(array $parameters): string;

	/**
	 * Decode path to array and validate existence of parameters
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public function decode(string $path): array;
}
