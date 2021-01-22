<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

final class PresetCollection implements PresetCollectionInterface
{
	/** @var array[]  */
	private $presets = [];

	/**
	 * {@inheritdoc}
	 */
	public function add(string $presetAlias, array $parameters): void
	{
		$this->presets[$presetAlias] = $parameters;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(string $presetAlias): bool
	{
		return isset($this->presets[$presetAlias]);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	public function get(string $presetAlias): array
	{
		if (!$this->has($presetAlias)) {
			throw new InvalidArgumentException(sprintf(
				'Preset with alias "%s" is not defined in collection, please check your configuration.',
				$presetAlias
			));
		}

		return $this->presets[$presetAlias];
	}
}
