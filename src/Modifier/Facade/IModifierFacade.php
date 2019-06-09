<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Intervention;
use SixtyEightPublishers;

interface IModifierFacade
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\IModifier[] $modifiers
	 *
	 * @return void
	 */
	public function setModifiers(array $modifiers): void;

	/**
	 * @param array[] $presets
	 *
	 * @return void
	 */
	public function setPresets(array $presets): void;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator[] $applicators
	 *
	 * @return void
	 */
	public function setApplicators(array $applicators): void;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator[] $validators
	 *
	 * @return void
	 */
	public function setValidators(array $validators): void;

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection
	 */
	public function getModifierCollection(): SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection;

	/**
	 * @param \Intervention\Image\Image                    $image
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array|string                                 $modifiers
	 *
	 * @return \Intervention\Image\Image
	 */
	public function modifyImage(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers): Intervention\Image\Image;

	/**
	 * @param array|string|NULL $modifiers
	 *
	 * @return array
	 */
	public function formatAsArray($modifiers): array;

	/**
	 * @param array|string|NULL $modifiers
	 *
	 * @return string
	 */
	public function formatAsString($modifiers): string;

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec
	 */
	public function getCodec(): SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec;
}
