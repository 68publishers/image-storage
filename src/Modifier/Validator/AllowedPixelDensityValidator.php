<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class AllowedPixelDensityValidator implements ValidatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate(ModifierValues $values, ConfigInterface $config): void
	{
		$allowedPixelDensities = $config[Config::ALLOWED_PIXEL_DENSITY];

		if (!is_array($allowedPixelDensities) || empty($allowedPixelDensities)) {
			return;
		}

		$pd = $values->getOptional(PixelDensity::class);

		if (NULL !== $pd && !in_array($pd, $allowedPixelDensities, FALSE)) {
			throw new ModifierException(sprintf(
				'Invalid pixel density modifier, %s is not supported.',
				$pd
			));
		}
	}
}
