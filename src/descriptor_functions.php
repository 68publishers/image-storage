<?php

declare(strict_types=1);

if (!function_exists('w_descriptor')) {
	function w_descriptor(int ...$widths): SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor
	{
		return new SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor(...$widths);
	}
}

if (!function_exists('x_descriptor')) {
	function x_descriptor(...$pixelDensities): SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor
	{
		if (empty($pixelDensities)) {
			return SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor::default();
		}

		return new SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor(...$pixelDensities);
	}
}
