<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

final class Fit extends AbstractModifier implements ParsableModifierInterface
{
	public const FILL = 'fill';
	public const STRETCH = 'stretch';
	public const CONTAIN = 'contain';
	public const CROP_CENTER = 'crop-center';
	public const CROP_LEFT = 'crop-left';
	public const CROP_RIGHT = 'crop-right';
	public const CROP_TOP = 'crop-top';
	public const CROP_TOP_LEFT = 'crop-top-left';
	public const CROP_TOP_RIGHT = 'crop-top-right';
	public const CROP_BOTTOM = 'crop-bottom';
	public const CROP_BOTTOM_LEFT = 'crop-bottom-left';
	public const CROP_BOTTOM_RIGHT = 'crop-bottom-right';

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

	/**
	 * {@inheritdoc}
	 */
	public function parseValue(string $value): string
	{
		if (!in_array($value, self::VALUES, TRUE)) {
			throw new ModifierException(sprintf(
				'Value "%s" is not a valid fit',
				$value
			));
		}

		return $value;
	}
}
