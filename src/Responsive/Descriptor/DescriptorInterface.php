<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

interface DescriptorInterface
{
	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @return array
	 */
	public function getDefaultModifiers(): array;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade $args
	 *
	 * @return string
	 */
	public function createSrcSet(ArgsFacade $args): string;
}
