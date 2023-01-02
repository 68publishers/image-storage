<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

interface SignatureStrategyInterface
{
	public function createToken(string $path): string;

	public function verifyToken(string $token, string $path): bool;
}
