<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

final class InvalidStateException extends \RuntimeException implements IException
{
	/**
	 * @param string $class
	 * @param string $method
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public static function missingMethodCall(string $class, string $method): self
	{
		return new static(sprintf(
			'The method %s::%s has not been called',
			$class,
			$method
		));
	}

	/**
	 * @param \Throwable $e
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public static function from(\Throwable $e): self
	{
		return new static(
			$e->getMessage(),
			$e->getCode(),
			$e
		);
	}
}
