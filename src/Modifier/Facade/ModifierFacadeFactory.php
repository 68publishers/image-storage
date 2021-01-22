<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use SixtyEightPublishers\ImageStorage\Modifier\Codec\Codec;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\PresetCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\RuntimeCachedCodec;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface;

final class ModifierFacadeFactory implements ModifierFacadeFactoryInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface  */
	private $presetCollectionFactory;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface  */
	private $modifierCollectionFactory;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionFactoryInterface       $presetCollectionFactory
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionFactoryInterface $modifierCollectionFactory
	 */
	public function __construct(PresetCollectionFactoryInterface $presetCollectionFactory, ModifierCollectionFactoryInterface $modifierCollectionFactory)
	{
		$this->presetCollectionFactory = $presetCollectionFactory;
		$this->modifierCollectionFactory = $modifierCollectionFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create(ConfigInterface $config): ModifierFacadeInterface
	{
		$presetCollection = $this->presetCollectionFactory->create();
		$modifierCollection = $this->modifierCollectionFactory->create();

		$codec = new RuntimeCachedCodec(
			new PresetCodec(
				new Codec($config, $modifierCollection),
				$presetCollection
			)
		);

		return new ModifierFacade($config, $codec, $presetCollection, $modifierCollection);
	}
}
