<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Nette;

interface IImageServer
{
	/**
	 * @param \Nette\Http\IRequest $request
	 *
	 * @return \Nette\Application\IResponse
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse;
}
