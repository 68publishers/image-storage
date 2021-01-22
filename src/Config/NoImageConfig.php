<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

final class NoImageConfig implements NoImageConfigInterface
{
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
	 * {@inheritDoc}
	 */
	public function getDefaultPath(): ?string
	{
		return $this->defaultPath;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPaths(): array
	{
		return $this->paths;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPatterns(): array
	{
		return $this->patterns;
	}
}
