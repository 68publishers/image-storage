<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

use Exception;
use Throwable;

final class ResponseException extends Exception implements ExceptionInterface
{
	public function __construct(string $message, int $code = 500, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function getHttpCode(): int
	{
		return $this->getCode();
	}
}
