<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use function assert;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function ksort;
use function sprintf;

final class Codec implements CodecInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ModifierCollectionInterface $modifierCollection,
    ) {}

    public function modifiersToPath(string|array $value): string
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                message: 'Can not transform value of type string, the value must be array<string, string|numeric|bool>.',
            );
        }

        if (empty($value)) {
            throw new InvalidArgumentException('Value can not be an empty array.');
        }

        $assigner = $this->config[Config::MODIFIER_ASSIGNER];
        $separator = $this->config[Config::MODIFIER_SEPARATOR];

        $assigner = empty($assigner) ? ':' : $assigner;
        $separator = empty($separator) ? ',' : $separator;

        $result = [];
        assert(is_string($assigner) && is_string($separator));

        ksort($value);

        foreach ($value as $k => $v) {
            $modifier = $this->modifierCollection->getByAlias($k);

            if (!$modifier instanceof ParsableModifierInterface) {
                if (true === (bool) $v) {
                    $result[] = $k;
                }

                continue;
            }

            $result[] = $k . $assigner . $v;
        }

        return implode($separator, $result);
    }

    public function pathToModifiers(string $value): array
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Value can not be an empty string.');
        }

        $parameters = [];
        $assigner = $this->config[Config::MODIFIER_ASSIGNER];
        $separator = $this->config[Config::MODIFIER_SEPARATOR];
        assert(is_string($assigner) && is_string($separator));

        $assigner = empty($assigner) ? ':' : $assigner;
        $separator = empty($separator) ? ',' : $separator;

        foreach (explode($separator, $value) as $modifier) {
            $modifier = explode($assigner, $modifier);
            $count = count($modifier);

            if (2 < $count) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" has an invalid format.',
                    $value,
                    implode($assigner, $modifier),
                ));
            }

            $modifierObject = $this->modifierCollection->getByAlias($modifier[0]);

            if (1 === $count && $modifierObject instanceof ParsableModifierInterface) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" must have a value.',
                    $value,
                    $modifierObject->getAlias(),
                ));
            }

            if (2 === $count && !$modifierObject instanceof ParsableModifierInterface) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" can not have a value.',
                    $value,
                    $modifierObject->getAlias(),
                ));
            }

            $parameters[$modifierObject->getAlias()] = 2 === $count ? $modifier[1] : true;
        }

        return $parameters;
    }

    public function expandModifiers(array|string $value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                message: 'Can not expand value of type string, the value must be array<string, string|numeric|bool>.',
            );
        }

        return $value;
    }
}
