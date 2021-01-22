<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec\Value;

final class PresetValue implements ValueInterface
{
	/** @var string  */
	private $presetName;

	/**
	 * @param string $presetName
	 */
	public function __construct(string $presetName)
	{
		$this->presetName = $presetName;
	}

	/**
	 * @return string
	 */
	public function getPresetName(): string
	{
		return $this->presetName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue(): string
	{
		return $this->getPresetName();
	}
}
