<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;

final class AllowedQualityValidator implements ValidatorInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate(ModifierValues $values, ConfigInterface $config): void
	{
		$allowedQualities = $config[Config::ALLOWED_QUALITIES];

		if (!is_array($allowedQualities) || empty($allowedQualities)) {
			return;
		}

		$quality = $values->getOptional(Quality::class);

		if (NULL !== $quality && !in_array($quality, $allowedQualities, FALSE)) {
			throw new ModifierException(sprintf(
				'Invalid quality modifier, %s is not supported.',
				$quality
			));
		}
	}
}
