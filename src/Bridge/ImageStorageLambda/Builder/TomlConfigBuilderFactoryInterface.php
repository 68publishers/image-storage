<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Builder;

interface TomlConfigBuilderFactoryInterface
{
	public function create(): TomlConfigBuilderInterface;
}
