<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use function array_pop;
use function explode;
use function hash_equals;
use function hash_hmac;
use function is_string;
use function ltrim;

final class SignatureStrategy implements SignatureStrategyInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly KnownModifiers $knownModifiers,
    ) {}

    public function createToken(string $path): ?string
    {
        if ($this->isKnown(path: $path)) {
            return null;
        }

        return $this->doCreateToken(path: $path);
    }

    public function verifyToken(string $token, string $path): bool
    {
        if ($this->isKnown($path)) {
            return true;
        }

        if ('' === $token) {
            return false;
        }

        return hash_equals($token, $this->doCreateToken(path: $path));
    }

    private function doCreateToken(string $path): string
    {
        $algo = $this->config[Config::SIGNATURE_ALGORITHM];
        $key = $this->config[Config::SIGNATURE_KEY];

        return hash_hmac(
            is_string($algo) ? $algo : 'sha256',
            ltrim($path, '/'),
            is_string($key) ? $key : '',
        );
    }

    private function isKnown(string $path): bool
    {
        if (!$this->config[Config::DISABLE_SIGNATURE_ON_KNOWN_MODIFIERS]) {
            return false;
        }

        $parts = explode('/', $path);
        array_pop($parts); # filename
        $modifiers = array_pop($parts);

        if (null === $modifiers) {
            return false;
        }

        return $this->knownModifiers->isKnown(
            modifiers: $modifiers,
        );
    }
}
