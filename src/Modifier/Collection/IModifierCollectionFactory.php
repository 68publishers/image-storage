<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

interface IModifierCollectionFactory
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection
	 */
	public function create(): IModifierCollection;
}
