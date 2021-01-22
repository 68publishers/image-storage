<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;

interface ModifierFacadeInterface
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface[] $modifiers
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
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface[] $applicators
	 *
	 * @return void
	 */
	public function setApplicators(array $applicators): void;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface[] $validators
	 *
	 * @return void
	 */
	public function setValidators(array $validators): void;

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface
	 */
	public function getModifierCollection(): ModifierCollectionInterface;

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface
	 */
	public function getCodec(): CodecInterface;

	/**
	 * @param \Intervention\Image\Image                           $image
	 * @param \SixtyEightPublishers\FileStorage\PathInfoInterface $info
	 * @param string|array                                        $modifiers
	 *
	 * @return \Intervention\Image\Image
	 */
	public function modifyImage(Image $image, PathInfoInterface $info, $modifiers): Image;
}
