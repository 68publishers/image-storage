<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

use Nette;
use SixtyEightPublishers;

final class PresetCollection implements IPresetCollection
{
	use Nette\SmartObject;

	/** @var array[]  */
	private $presets = [];

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollection **************/

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
	 */
	public function get(string $presetAlias): array
	{
		if (!$this->has($presetAlias)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Preset with alias "%s" is not defined in collection, please check your configuration.',
				$presetAlias
			));
		}

		return $this->presets[$presetAlias];
	}
}
