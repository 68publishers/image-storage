<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

interface ModifierInterface
{
	public function getName(): string;

	public function getAlias(): string;
}
