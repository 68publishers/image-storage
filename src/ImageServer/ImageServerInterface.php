<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;

interface ImageServerInterface
{
	/**
	 * Returns response object
	 *
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface $request
	 *
	 * @return mixed|object
	 */
	public function getImageResponse(RequestInterface $request);
}
