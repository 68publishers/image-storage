<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer\Response;

use Nette;
use SixtyEightPublishers;

final class ErrorResponse extends Nette\Application\Responses\TextResponse
{
	use Nette\SmartObject;

	/** @var int  */
	private $code;

	/**
	 * @param string $message
	 * @param int    $code
	 */
	public function __construct(string $message, int $code = Nette\Http\IResponse::S500_INTERNAL_SERVER_ERROR)
	{
		parent::__construct($message);

		$this->code = $code;
	}

	/************** interface \Nette\Application\IResponse **************/

	/**
	 * {@inheritdoc}
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		$httpResponse->setCode($this->code);

		parent::send($httpRequest, $httpResponse);
	}
}
