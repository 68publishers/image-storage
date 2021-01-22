<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer\Request;

interface RequestInterface
{
	/**
	 * @return string
	 */
	public function getUrlPath(): string;

	/**
	 * @param string $name
	 *
	 * @return mixed|NULL
	 */
	public function getQueryParameter(string $name);

	/**
	 * @return mixed|object
	 */
	public function getOriginalRequest();
}
