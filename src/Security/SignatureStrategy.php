<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;

final class SignatureStrategy implements SignatureStrategyInterface
{
	/** @var \SixtyEightPublishers\FileStorage\Config\ConfigInterface  */
	private $config;

	/**
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface $config
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->config = $config;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createToken(string $path): string
	{
		return hash_hmac(
			$this->config[Config::SIGNATURE_ALGORITHM],
			ltrim($path, '/'),
			(string) $this->config[Config::SIGNATURE_KEY]
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function verifyToken(string $token, string $path): bool
	{
		return hash_equals($token, $this->createToken($path));
	}
}
