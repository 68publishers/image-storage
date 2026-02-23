<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

interface CodecInterface
{
    /**
     * @param string|array<string, string|numeric|bool> $value
     */
    public function modifiersToPath(string|array $value): string;

    /**
     * @return array<string, string|numeric|bool>
     */
    public function pathToModifiers(string $value): array;

    /**
     * @param string|array<string, string|numeric|bool> $value
     *
     * @return array<string, string|numeric|bool>
     */
    public function expandModifiers(string|array $value): array;
}
