<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

interface IParsableModifier extends IModifier
{
	/**
	 * Returns NULL if value is not valid, otherwise returns original passed or modified value
	 *
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function parseValue(string $value);
}
