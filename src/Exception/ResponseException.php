<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

use Exception;
use Throwable;

final class ResponseException extends Exception implements ExceptionInterface
{
	/**
	 * @param string          $message
	 * @param int             $code
	 * @param \Throwable|NULL $previous
	 */
	public function __construct(string $message, int $code = 500, Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Alias
	 *
	 * @return int
	 */
	public function getHttpCode(): int
	{
		return $this->getCode();
	}
}
