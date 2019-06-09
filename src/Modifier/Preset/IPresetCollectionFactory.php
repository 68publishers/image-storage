<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Preset;

interface IPresetCollectionFactory
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollection
	 */
	public function create(): IPresetCollection;
}
