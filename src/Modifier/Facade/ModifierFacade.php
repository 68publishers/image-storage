<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Nette;
use Intervention;
use SixtyEightPublishers;

final class ModifierFacade implements IModifierFacade
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec  */
	private $codec;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollection  */
	private $presetCollection;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection  */
	private $modifierCollection;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator[] */
	private $applicators = [];

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator[] */
	private $validators = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodecFactory                   $codecFactory
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollectionFactory       $presetCollectionFactory
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollectionFactory $modifierCollectionFactory
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodecFactory $codecFactory,
		SixtyEightPublishers\ImageStorage\Modifier\Preset\IPresetCollectionFactory $presetCollectionFactory,
		SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollectionFactory $modifierCollectionFactory
	) {
		$this->presetCollection = $presetCollectionFactory->create();
		$this->modifierCollection = $modifierCollectionFactory->create();
		$this->codec = $codecFactory->create($this->modifierCollection);
	}

	/***************** interface \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade *****************/

	/**
	 * {@inheritdoc}
	 */
	public function setModifiers(array $modifiers): void
	{
		foreach ($modifiers as $modifier) {
			if (!$modifier instanceof SixtyEightPublishers\ImageStorage\Modifier\IModifier) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					SixtyEightPublishers\ImageStorage\Modifier\IModifier::class
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
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
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
			if (!$applicator instanceof SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					SixtyEightPublishers\ImageStorage\Modifier\Applicator\IModifierApplicator::class
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
			if (!$validator instanceof SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'Argument passed into method %s must be array of %s.',
					__METHOD__,
					SixtyEightPublishers\ImageStorage\Modifier\Validator\IValidator::class
				));
			}

			$this->validators[] = $validator;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModifierCollection(): SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection
	{
		return $this->modifierCollection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function modifyImage(Intervention\Image\Image $image, SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers): Intervention\Image\Image
	{
		$modifiers = $this->formatAsArray($modifiers);

		if (empty($modifiers)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Can not modify image, modifiers are empty.'
			));
		}

		$modifiers = $this->modifierCollection->parseValues($modifiers);

		foreach ($this->validators as $validator) {
			$validator->validate($modifiers);
		}

		foreach ($this->applicators as $applicator) {
			$image  = $applicator->apply($image, $info, $modifiers);
		}

		return $image;
	}

	/**
	 * {@inheritdoc}
	 */
	public function formatAsArray($modifiers): array
	{
		return $this->codec->decode($this->formatAsString($modifiers));
	}

	/**
	 * {@inheritdoc}
	 */
	public function formatAsString($modifiers): string
	{
		if (!empty($modifiers) && !is_array($modifiers)) {
			$modifiers = $this->presetCollection->get((string) $modifiers);
		}

		return $this->codec->encode($modifiers ?? []);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCodec(): SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec
	{
		return $this->codec;
	}
}
