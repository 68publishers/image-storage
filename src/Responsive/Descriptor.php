<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use Nette;

final class Descriptor
{
	use Nette\SmartObject;

	/** @var NULL|int|float|float  */
	private $descriptor;

	/** @var float  */
	private $pd;

	/**
	 * @param NULL|int|float|float $descriptor
	 * @param int|float|string     $pd
	 */
	public function __construct($descriptor, $pd)
	{
		Nette\Utils\Validators::assert($descriptor, 'null|numeric');
		Nette\Utils\Validators::assert($pd, 'numeric');

		$this->descriptor = $descriptor;
		$this->pd = (float) $pd;
	}

	/**
	 * @return string
	 */
	public function x(): string
	{
		return NULL !== $this->descriptor
			? sprintf('%sx', number_format((float) $this->descriptor, 1, '.', ''))
			: '';
	}

	/**
	 * @return string
	 */
	public function w(): string
	{
		return NULL !== $this->descriptor
			? sprintf('%dw', (int) $this->descriptor)
			: '';
	}

	/**
	 * @return float
	 */
	public function pd(): float
	{
		return $this->pd;
	}
}
