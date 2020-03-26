<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use Nette;
use SixtyEightPublishers;

final class AllowedPixelDensityValidator implements IValidator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config $config
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Config $config)
	{
		$this->config = $config;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator **************/

	/**
	 * {@inheritdoc}
	 */
	public function validate(SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): void
	{
		$allowedPixelDensities = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::ALLOWED_PIXEL_DENSITY];

		if (!is_array($allowedPixelDensities) || empty($allowedPixelDensities)) {
			return;
		}

		$pd = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\PixelDensity::class);

		if (NULL !== $pd && !in_array($pd, $allowedPixelDensities, FALSE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Invalid pixel density modifier, %s is not supported.',
				$pd
			));
		}
	}
}
