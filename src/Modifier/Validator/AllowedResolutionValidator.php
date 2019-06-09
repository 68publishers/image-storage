<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Validator;

use Nette;
use SixtyEightPublishers;

final class AllowedResolutionValidator implements IValidator
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
		$allowedResolutions = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ALLOWED_RESOLUTIONS];

		if (!is_array($allowedResolutions) || empty($allowedResolutions)) {
			return;
		}

		$width = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Width::class);
		$height = $values->getOptional(SixtyEightPublishers\ImageStorage\Modifier\Height::class);

		if ((NULL !== $width || NULL !== $height) && !in_array($width . 'x' . $height, $allowedResolutions, TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Invalid combination of width and height modifiers, %s is not supported.',
				$width . 'x' . $height
			));
		}
	}
}
