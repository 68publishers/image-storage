<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers;

interface IValidator
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ModifierException
	 */
	public function validate(SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): void;
}
