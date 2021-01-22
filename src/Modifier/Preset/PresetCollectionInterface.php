<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface PresetCollectionInterface
{
	/**
	 * @param string $presetAlias
	 * @param array  $parameters
	 */
	public function add(string $presetAlias, array $parameters): void;

	/**
	 * @param string $presetAlias
	 *
	 * @return bool
	 */
	public function has(string $presetAlias): bool;

	/**
	 * @param string $presetAlias
	 *
	 * @return array
	 */
	public function get(string $presetAlias): array;
}
