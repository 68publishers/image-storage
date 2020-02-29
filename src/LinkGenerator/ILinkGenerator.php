<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers;

interface ILinkGenerator extends SixtyEightPublishers\ImageStorage\Security\ISignatureStrategyAware
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array|string                                 $modifiers
	 *
	 * @return string
	 */
	public function link(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                         $info
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param NULL|array|string                                                    $modifiers
	 *
	 * @return string
	 */
	public function srcSet(SixtyEightPublishers\ImageStorage\ImageInfo $info, SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor, $modifiers = NULL): string;
}
