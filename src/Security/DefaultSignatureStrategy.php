<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

use Nette;
use SixtyEightPublishers;

final class DefaultSignatureStrategy implements ISignatureStrategy
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config $config
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Config $config)
	{
		$this->config = $config;
	}

	/************ interface \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy ************/

	/**
	 * {@inheritDoc}
	 */
	public function createToken(string $path): string
	{
		return hash_hmac(
			$this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_ALGORITHM],
			ltrim($path, '/'),
			(string) $this->config[SixtyEightPublishers\ImageStorage\Config\Config::SIGNATURE_KEY]
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
