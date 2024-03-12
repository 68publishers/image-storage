<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

use SixtyEightPublishers\FileStorage\Config\Config as FileStorageConfig;

final class Config extends FileStorageConfig
{
    public const SIGNATURE_PARAMETER_NAME = 'signature_parameter_name';
    public const SIGNATURE_KEY = 'signature_key';
    public const SIGNATURE_ALGORITHM = 'signature_algorithm';
    public const MODIFIER_SEPARATOR = 'modifier_separator';
    public const MODIFIER_ASSIGNER = 'modifier_assigner';
    public const ALLOWED_PIXEL_DENSITY = 'allowed_pixel_density';
    public const ALLOWED_RESOLUTIONS = 'allowed_resolutions';
    public const ALLOWED_QUALITIES = 'allowed_qualities';
    public const ENCODE_QUALITY = 'encode_quality';
    public const CACHE_MAX_AGE = 'cache_max_age';

    protected array $config = [
        self::BASE_PATH => '',
        self::HOST => null,
        self::MODIFIER_SEPARATOR => ',',
        self::MODIFIER_ASSIGNER => ':',
        self::VERSION_PARAMETER_NAME => '_v',
        self::SIGNATURE_PARAMETER_NAME => '_s',
        self::SIGNATURE_KEY => null,
        self::SIGNATURE_ALGORITHM => 'sha256',
        self::ALLOWED_PIXEL_DENSITY => [],
        self::ALLOWED_RESOLUTIONS => [],
        self::ALLOWED_QUALITIES => [],
        self::ENCODE_QUALITY => 90,
        self::CACHE_MAX_AGE => 31536000,
    ];
}
