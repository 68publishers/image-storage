<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

interface ModifierCollectionFactoryInterface
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface
	 */
	public function create(): ModifierCollectionInterface;
}
