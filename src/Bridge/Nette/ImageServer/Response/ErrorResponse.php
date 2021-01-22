<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Response;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Application\IResponse as ApplicationResponse;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;

final class ErrorResponse implements ApplicationResponse
{
	/** @var \SixtyEightPublishers\ImageStorage\Exception\ResponseException  */
	private $exception;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Exception\ResponseException $exception
	 */
	public function __construct(ResponseException $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Exception\ResponseException
	 */
	public function getException(): ResponseException
	{
		return $this->exception;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		$httpResponse->setCode($this->exception->getHttpCode());

		echo json_encode([
			'code' => $this->exception->getHttpCode(),
			'message' => $this->exception->getMessage(),
		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}
}
