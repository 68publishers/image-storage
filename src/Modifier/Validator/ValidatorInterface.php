<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

interface ValidatorInterface
{
	/**
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ModifierException
	 */
	public function validate(ModifierValues $values, ConfigInterface $config): void;
}
