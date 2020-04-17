<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

use Nette;

final class NoImageConfig
{
	use Nette\SmartObject;

	/** @var string|NULL  */
	private $defaultPath;

	/** @var string[]  */
	private $paths;

	/** @var string[]  */
	private $patterns;

	/**
	 * @param string|NULL $defaultPath
	 * @param array       $paths
	 * @param array       $patterns
	 */
	public function __construct(?string $defaultPath, array $paths, array $patterns)
	{
		$this->defaultPath = $defaultPath;
		$this->paths = $paths;
		$this->patterns = $patterns;
	}

	/**
	 * @return NULL|string
	 */
	public function getDefaultPath(): ?string
	{
		return $this->defaultPath;
	}

	/**
	 * @return string[]
	 */
	public function getPaths(): array
	{
		return $this->paths;
	}

	/**
	 * @return string[]
	 */
	public function getPatterns(): array
	{
		return $this->patterns;
	}
}
