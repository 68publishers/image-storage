<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Helper;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use function array_key_exists;
use function array_keys;
use function array_search;
use function array_unique;
use function array_values;
use function in_array;

final class SupportedType
{
    /** @var array<string, string> */
    private static array $supportedTypes = [
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'pjpg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
    ];

    /** @var array{0: string, 1:string} */
    private static array $default = ['jpg', 'image/jpeg'];

    private function __construct() {}

    /**
     * @return array<string>
     */
    public static function getSupportedTypes(): array
    {
        return array_values(array_unique(self::$supportedTypes));
    }

    /**
     * @return array<string>
     */
    public static function getSupportedExtensions(): array
    {
        return array_keys(self::$supportedTypes);
    }

    public static function getDefaultExtension(): string
    {
        return self::$default[0];
    }

    public static function getDefaultType(): string
    {
        return self::$default[1];
    }

    public static function isTypeSupported(string $type): bool
    {
        return in_array($type, self::$supportedTypes, true);
    }

    public static function isExtensionSupported(string $extension): bool
    {
        return array_key_exists($extension, self::$supportedTypes);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getTypeByExtension(string $extension): string
    {
        if (!self::isExtensionSupported($extension)) {
            throw new InvalidArgumentException(sprintf(
                'Extension .%s is not supported.',
                $extension,
            ));
        }

        return self::$supportedTypes[$extension];
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getExtensionByType(string $type): string
    {
        if (!self::isTypeSupported($type)) {
            throw new InvalidArgumentException(sprintf(
                'Mime type %s is not supported.',
                $type,
            ));
        }

        return (string) array_search($type, self::$supportedTypes, true);
    }

    /**
     * file extensions as keys and MimeTypes as values
     *
     * @param array<string, string> $types
     */
    public static function setSupportedTypes(array $types): void
    {
        self::$supportedTypes = $types;
    }

    public static function setDefault(string $extension, string $type): void
    {
        self::$default = [$extension, $type];
    }
}
