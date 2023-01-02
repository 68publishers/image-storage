<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer\Request;

interface RequestInterface
{
	public function getUrlPath(): string;

	public function getQueryParameter(string $name): array|string|null;

	public function getOriginalRequest(): object;
}
