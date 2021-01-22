<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

interface ValidatorInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface              $config
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ModifierException
	 */
	public function validate(ModifierValues $values, ConfigInterface $config): void;
}
