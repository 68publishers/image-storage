<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use Nette;
use SixtyEightPublishers;

final class AllowedQualityValidator implements IValidator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env $env
	 */
	public function __construct(SixtyEightPublishers\ImageStorage\Config\Env $env)
	{
		$this->env = $env;
	}

	/************** interface \SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator **************/

	/**
	 * {@inheritdoc}
	 */
	public function validate(SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues $values): void
	{
		$allowedQualities = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ALLOWED_QUALITIES];

		if (!is_array($allowedQualities) || empty($allowedQualities)) {
			return;
		}

		$quality = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Quality::class);

		if (NULL !== $quality && !in_array($quality, $allowedQualities, FALSE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Invalid quality modifier, %s is not supported.',
				$quality
			));
		}
	}
}
