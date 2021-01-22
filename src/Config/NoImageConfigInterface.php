<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

interface NoImageConfigInterface
{
	/**
	 * @return string|NULL
	 */
	public function getDefaultPath(): ?string;

	/**
	 * @return string[]
	 */
	public function getPaths(): array;

	/**
	 * @return string[]
	 */
	public function getPatterns(): array;
}
