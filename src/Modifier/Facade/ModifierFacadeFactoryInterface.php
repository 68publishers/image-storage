<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;

interface ModifierFacadeFactoryInterface
{
    public function create(ConfigInterface $config): ModifierFacadeInterface;
}
