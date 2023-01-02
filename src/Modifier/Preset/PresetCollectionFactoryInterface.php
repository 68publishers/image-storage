<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface PresetCollectionFactoryInterface
{
	public function create(): PresetCollectionInterface;
}
