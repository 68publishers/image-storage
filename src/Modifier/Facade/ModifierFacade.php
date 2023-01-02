<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use function sprintf;
use function is_array;

final class ModifierFacade implements ModifierFacadeInterface
{
	/** @var array<ModifierApplicatorInterface> */
	private array $applicators = [];

	/** @var array<ValidatorInterface> */
	private array $validators = [];

	public function __construct(
		private readonly ConfigInterface $config,
		private readonly CodecInterface $codec,
		private readonly PresetCollectionInterface $presetCollection,
		private readonly ModifierCollectionInterface $modifierCollection,
	) {
	}

	public function setModifiers(array $modifiers): void
	{
		foreach ($modifiers as $modifier) {
			if (!$modifier instanceof ModifierInterface) {
				throw new InvalidArgumentException(sprintf(
					'The argument passed into the method %s() must be an array of %s.',
					__METHOD__,
					ModifierInterface::class
				));
			}

			$this->modifierCollection->add($modifier);
		}
	}

	public function setPresets(array $presets): void
	{
		foreach ($presets as $name => $preset) {
			if (!is_array($preset)) {
				throw new InvalidArgumentException(sprintf(
					'The argument passed into the method %s() must be an array of arrays (a preset name => an array of modifier aliases).',
					__METHOD__
				));
			}

			$this->presetCollection->add((string) $name, $preset);
		}
	}

	public function setApplicators(array $applicators): void
	{
		foreach ($applicators as $applicator) {
			if (!$applicator instanceof ModifierApplicatorInterface) {
				throw new InvalidArgumentException(sprintf(
					'The argument passed into the method %s() must be an array of %s.',
					__METHOD__,
					ModifierApplicatorInterface::class
				));
			}

			$this->applicators[] = $applicator;
		}
	}

	public function setValidators(array $validators): void
	{
		foreach ($validators as $validator) {
			if (!$validator instanceof ValidatorInterface) {
				throw new InvalidArgumentException(sprintf(
					'The argument passed into the method %s() must be an array of %s.',
					__METHOD__,
					ValidatorInterface::class
				));
			}

			$this->validators[] = $validator;
		}
	}

	public function getModifierCollection(): ModifierCollectionInterface
	{
		return $this->modifierCollection;
	}

	public function getCodec(): CodecInterface
	{
		return $this->codec;
	}

	public function modifyImage(Image $image, PathInfoInterface $info, string|array $modifiers): Image
	{
		if (!is_array($modifiers)) {
			$modifiers = $this->getCodec()->decode(new PresetValue($modifiers));
		}

		if (empty($modifiers)) {
			throw new InvalidArgumentException('Unable to modify the image, modifiers are empty.');
		}

		$values = $this->modifierCollection->parseValues($modifiers);

		foreach ($this->validators as $validator) {
			$validator->validate($values, $this->config);
		}

		foreach ($this->applicators as $applicator) {
			$image = $applicator->apply($image, $info, $values, $this->config);
		}

		return $image;
	}
}
