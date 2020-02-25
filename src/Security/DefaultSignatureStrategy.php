<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

use Nette;

final class DefaultSignatureStrategy implements ISignatureStrategy
{
	use Nette\SmartObject;

	/** @var string  */
	private $privateKey;

	/** @var string  */
	private $algorithm;

	/**
	 * @param string $privateKey
	 * @param string $algorithm
	 */
	public function __construct(string $privateKey, string $algorithm = 'sha256')
	{
		$this->privateKey = $privateKey;
		$this->algorithm = $algorithm;
	}

	/************ interface \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy ************/

	/**
	 * {@inheritDoc}
	 */
	public function createToken(string $path): string
	{
		return hash_hmac(
			$this->algorithm,
			ltrim($path, '/'),
			$this->privateKey
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
