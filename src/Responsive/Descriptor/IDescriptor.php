<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

interface IDescriptor
{
	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade $args
	 *
	 * @return string
	 */
	public function createSrcSet(ArgsFacade $args): string;
}
