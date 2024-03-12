<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Intervention\Image;

final class MultiFrameCommandList
{
    public const LIST = [
        'Circle',
        'Ellipse',
        'Line',
        'Orientate',
        'Polygon',
        'Rectangle',
        'Text',
        'Blur',
        'Brightness',
        'Colorize',
        'Contrast',
        'Crop',
        'Fill',
        'Fit',
        'Flip',
        'Gamma',
        'Greyscale',
        'Heighten',
        'Insert',
        'Interlace',
        'Invert',
        'LimitColors',
        'Mask',
        'Opacity',
        'Pixelate',
        'Pixel',
        'ResizeCanvas',
        'Resize',
        'Rotate',
        'Sharpen',
        'Trim',
        'Widen',
    ];

    private function __construct() {}
}
