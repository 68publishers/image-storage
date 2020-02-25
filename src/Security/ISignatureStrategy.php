<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

interface ISignatureStrategy
{
	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public function createToken(string $path): string;

	/**
	 * @param string $token
	 * @param string $path
	 *
	 * @return bool
	 */
	public function verifyToken(string $token, string $path): bool;
}
