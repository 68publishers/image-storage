<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

interface ImageServerInterface
{
	public function getImageResponse(RequestInterface $request): object;
}
