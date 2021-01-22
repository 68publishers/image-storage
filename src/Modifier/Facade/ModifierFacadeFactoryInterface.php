<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;

interface ModifierFacadeFactoryInterface
{
	/**
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface $config
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface
	 */
	public function create(ConfigInterface $config): ModifierFacadeInterface;
}
