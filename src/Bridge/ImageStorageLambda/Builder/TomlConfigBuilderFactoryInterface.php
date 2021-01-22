<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder;

interface TomlConfigBuilderFactoryInterface
{
	/**
	 * @return \SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder\TomlConfigBuilderInterface
	 */
	public function create(): TomlConfigBuilderInterface;
}
