<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\PathInfoInterface as BasePathInfoInterface;

interface PathInfoInterface extends BasePathInfoInterface
{
	/**
	 * @return NULL|string|array
	 */
	public function getModifiers();

	/**
	 * @param NULL|string|array $modifiers
	 *
	 * @return $this
	 */
	public function withModifiers($modifiers);

	/**
	 * Creates new object with encoded modifiers, the modifier will me decoded into an array
	 *
	 * @param string $modifiers
	 *
	 * @return $this
	 */
	public function withEncodedModifiers(string $modifiers);
}
