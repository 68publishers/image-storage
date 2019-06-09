<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use Nette;

final class DescriptorIterator implements \IteratorAggregate
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\Descriptor[]  */
	private $descriptors = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor[] $descriptors
	 */
	public function __construct(array $descriptors)
	{
		foreach ($descriptors as $descriptor) {
			$this->addDescriptor($descriptor);
		}
	}

	/**
	 * @return \SixtyEightPublishers\ImageStorage\Responsive\Descriptor[]
	 */
	public function toArray(): array
	{
		return $this->descriptors;
	}

	/**
	 * Callback's argument is Descriptor instance
	 *
	 * @param callable $function
	 *
	 * @return string
	 */
	public function concat(callable $function): string
	{
		return implode(', ', array_map($function, $this->toArray()));
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor $descriptor
	 *
	 * @return void
	 */
	private function addDescriptor(Descriptor $descriptor): void
	{
		$this->descriptors[] = $descriptor;
	}

	/****************** interface \IteratorAggregate ******************/

	/**
	 * {@inheritdoc}
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->toArray());
	}
}
