<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;

interface ImageServerInterface
{
	public function getImageResponse(RequestInterface $request): object;
}
