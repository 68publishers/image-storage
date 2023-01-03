<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer;

use JsonException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Application\IResponse as ApplicationResponse;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use function json_encode;

final class ErrorResponse implements ApplicationResponse
{
	public function __construct(
		private readonly ResponseException $exception,
	) {
	}

	public function getException(): ResponseException
	{
		return $this->exception;
	}

	/**
	 * @throws JsonException
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		$httpResponse->setCode($this->exception->getHttpCode());

		echo json_encode([
			'code' => $this->exception->getHttpCode(),
			'message' => $this->exception->getMessage(),
		], JSON_THROW_ON_ERROR);
	}
}
