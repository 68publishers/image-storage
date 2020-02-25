<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Nette;
use SixtyEightPublishers;

final class ExternalImageServer implements IImageServer
{
	use Nette\SmartObject,
		SixtyEightPublishers\ImageStorage\Security\TSignatureStrategyAware;

	/************** interface \SixtyEightPublishers\ImageStorage\ImageServer\IImageServer **************/

	/**
	 * {@inheritdoc}
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse
	{
		throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(
			'Image Server is external service!'
		);
	}
}
