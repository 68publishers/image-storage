<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface PresetCollectionFactoryInterface
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface
	 */
	public function create(): PresetCollectionInterface;
}
