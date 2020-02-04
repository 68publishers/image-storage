<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

interface IModifier
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getAlias(): string;

	/**
	 * Returns NULL if value is not valid, otherwise returns original passed or modified value
	 *
	 * @param string $value
	 *
	 * @return mixed
	 */
	public function parseValue(string $value);
}
