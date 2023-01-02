<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

interface NoImageConfigInterface
{
	public function getDefaultPath(): ?string;

	/**
	 * @return array<string, string>
	 */
	public function getPaths(): array;

	/**
	 * @return array<string, string>
	 */
	public function getPatterns(): array;
}
