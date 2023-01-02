<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

interface ModifierCollectionFactoryInterface
{
	public function create(): ModifierCollectionInterface;
}
