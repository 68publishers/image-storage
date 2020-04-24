<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers;

final class Fit extends AbstractModifier implements IParsableModifier
{
	public const    FILL = 'fill',
					STRETCH = 'stretch',
					CONTAIN = 'contain',
					CROP_CENTER = 'crop-center',
					CROP_LEFT = 'crop-left',
					CROP_RIGHT = 'crop-right',
					CROP_TOP = 'crop-top',
					CROP_TOP_LEFT = 'crop-top-left',
					CROP_TOP_RIGHT = 'crop-top-right',
					CROP_BOTTOM = 'crop-bottom',
					CROP_BOTTOM_LEFT = 'crop-bottom-left',
					CROP_BOTTOM_RIGHT = 'crop-bottom-right';

	public const VALUES = [
		self::FILL,
		self::STRETCH,
		self::CONTAIN,
		self::CROP_CENTER,
		self::CROP_LEFT,
		self::CROP_RIGHT,
		self::CROP_TOP,
		self::CROP_TOP_LEFT,
		self::CROP_TOP_RIGHT,
		self::CROP_BOTTOM,
		self::CROP_BOTTOM_LEFT,
		self::CROP_BOTTOM_RIGHT,
	];

	/** @var string  */
	protected $alias = 'f';

	/****************** interface \SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier ******************/

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): string
	{
		if (!in_array($value, self::VALUES, TRUE)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\ModifierException(sprintf(
				'Value "%s" is not a valid fit',
				$value
			));
		}

		return $value;
	}
}
