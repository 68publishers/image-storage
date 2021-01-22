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

final class ModifierFacade implements ModifierFacadeInterface
{
	/** @var \SixtyEightPublishers\FileStorage\Config\ConfigInterface  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface  */
	private $codec;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface  */
	private $presetCollection;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface  */
	private $modifierCollection;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface[] */
	private $applicators = [];

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface[] */
	private $validators = [];

	/**
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface                           $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface                   $codec
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface       $presetCollection
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface $modifierCollection
	 */
	public function __construct(ConfigInterface $config, CodecInterface $codec, PresetCollectionInterface $presetCollection, ModifierCollectionInterface $modifierCollection)
	{
		$this->config = $config;
		$this->codec = $codec;
		$this->presetCollection = $presetCollection;
		$this->modifierCollection = $modifierCollection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setModifiers(array $modifiers): void
	{
		foreach ($modifiers as $modifier) {
			if (!$modifier instanceof ModifierInterface) {
				throw new InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					ModifierInterface::class
				));
			}

			$this->modifierCollection->add($modifier);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPresets(array $presets): void
	{
		foreach ($presets as $name => $preset) {
			if (!is_array($preset)) {
				throw new InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of arrays (preset name => array of modifier aliases).',
					__METHOD__
				));
			}

			$this->presetCollection->add((string) $name, $preset);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setApplicators(array $applicators): void
	{
		foreach ($applicators as $applicator) {
			if (!$applicator instanceof ModifierApplicatorInterface) {
				throw new InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					ModifierApplicatorInterface::class
				));
			}

			$this->applicators[] = $applicator;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValidators(array $validators): void
	{
		foreach ($validators as $validator) {
			if (!$validator instanceof ValidatorInterface) {
				throw new InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					ValidatorInterface::class
				));
			}

			$this->validators[] = $validator;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModifierCollection(): ModifierCollectionInterface
	{
		return $this->modifierCollection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCodec(): CodecInterface
	{
		return $this->codec;
	}

	/**
	 * {@inheritdoc}
	 */
	public function modifyImage(Image $image, PathInfoInterface $info, $modifiers): Image
	{
		if (!(is_array($modifiers))) {
			$modifiers = $this->getCodec()->decode(new PresetValue($modifiers));
		}

		if (empty($modifiers)) {
			throw new InvalidArgumentException('Can\'t modify an image, modifiers are empty.');
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
