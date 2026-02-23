<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use JsonException;
use function json_encode;

final class RuntimeCachedCodec implements CodecInterface
{
    /**
     * @var array{
     *     modifiersToPath: array<string, string>,
     *     pathToModifiers: array<string, array<string, string|numeric|bool>>,
     *     expandModifiers: array<string, array<string, string|numeric|bool>>,
     * }
     */
    private array $cache = [
        'modifiersToPath' => [],
        'pathToModifiers' => [],
        'expandModifiers' => [],
    ];

    public function __construct(
        private readonly CodecInterface $codec,
    ) {}

    /**
     * @throws JsonException
     */
    public function modifiersToPath(string|array $value): string
    {
        $key = json_encode($value, JSON_THROW_ON_ERROR);

        return $this->cache['modifiersToPath'][$key] ??= $this->codec->modifiersToPath($value);
    }

    public function pathToModifiers(string $value): array
    {
        return $this->cache['pathToModifiers'][$value] ??= $this->codec->pathToModifiers($value);
    }

    /**
     * @throws JsonException
     */
    public function expandModifiers(array|string $value): array
    {
        $key = json_encode($value, JSON_THROW_ON_ERROR);

        return $this->cache['expandModifiers'][$key] ??= $this->codec->expandModifiers($value);
    }
}
