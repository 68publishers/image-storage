<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Cleaner;

interface IStorageCleaner
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @param string|NULL $namespace
	 * @param bool        $cacheOnly
	 *
	 * @return int
	 */
	public function getCount(?string $namespace, bool $cacheOnly = FALSE): int;

	/**
	 * @param string|NULL $namespace
	 * @param bool        $cacheOnly
	 */
	public function clean(?string $namespace = NULL, bool $cacheOnly = FALSE): void;
}
