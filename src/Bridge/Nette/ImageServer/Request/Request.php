<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Request;

use Nette\Http\IRequest;
use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;

final class Request implements RequestInterface
{
	/** @var \Nette\Http\IRequest  */
	private $request;

	/**
	 * @param \Nette\Http\IRequest $request
	 */
	public function __construct(IRequest $request)
	{
		$this->request = $request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUrlPath(): string
	{
		return $this->request->getUrl()->getPath();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getQueryParameter(string $name)
	{
		return $this->request->getUrl()->getQueryParameter($name);
	}

	/**
	 * @return \Nette\Http\IRequest
	 */
	public function getOriginalRequest(): IRequest
	{
		return $this->request;
	}
}
