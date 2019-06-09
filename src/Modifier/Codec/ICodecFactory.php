<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers;

interface ICodecFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec
	 */
	public function create(SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection): ICodec;
}
