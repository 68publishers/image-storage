<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

final class IllegalMethodCallException extends \BadMethodCallException implements IException
{
	/**
	 * @param string $method
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Exception\IllegalMethodCallException
	 */
	public static function notAllowed(string $method): self
	{
		return new self(sprintf(
			'Calling the method %s is not allowed',
			$method
		));
	}
}
