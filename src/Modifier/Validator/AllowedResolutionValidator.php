<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class AllowedResolutionValidator implements ValidatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate(ModifierValues $values, ConfigInterface $config): void
	{
		$allowedResolutions = $config[Config::ALLOWED_RESOLUTIONS];

		if (!is_array($allowedResolutions) || empty($allowedResolutions)) {
			return;
		}

		$width = $values->getOptional(Width::class);
		$height = $values->getOptional(Height::class);

		if ((NULL !== $width || NULL !== $height) && !in_array($width . 'x' . $height, $allowedResolutions, TRUE)) {
			throw new ModifierException(sprintf(
				'Invalid combination of width and height modifiers, %s is not supported.',
				$width . 'x' . $height
			));
		}
	}
}
