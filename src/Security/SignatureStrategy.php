<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use function hash_equals;
use function hash_hmac;
use function is_string;
use function ltrim;

final class SignatureStrategy implements SignatureStrategyInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
    ) {}

    public function createToken(string $path): string
    {
        $algo = $this->config[Config::SIGNATURE_ALGORITHM];
        $key = $this->config[Config::SIGNATURE_KEY];

        return hash_hmac(
            is_string($algo) ? $algo : 'sha256',
            ltrim($path, '/'),
            is_string($key) ? $key : '',
        );
    }

    public function verifyToken(string $token, string $path): bool
    {
        return hash_equals($token, $this->createToken($path));
    }
}
