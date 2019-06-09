<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers;

interface ILinkGenerator
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param NULL|array|string                            $modifiers
	 *
	 * @return string
	 */
	public function link(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): string;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param NULL|array|string                            $modifiers
	 *
	 * @return string
	 */
	public function srcSet(SixtyEightPublishers\ImageStorage\ImageInfo $info, $modifiers = NULL): string;
}
