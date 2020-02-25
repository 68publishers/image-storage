<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Nette;
use SixtyEightPublishers;

interface IImageServer extends SixtyEightPublishers\ImageStorage\Security\ISignatureStrategyAware
{
	/**
	 * @param \Nette\Http\IRequest $request
	 *
	 * @return \Nette\Application\IResponse
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse;
}
