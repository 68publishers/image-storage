<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use Stringable;
use function assert;
use function count;
use function explode;
use function gettype;
use function implode;
use function is_array;
use function ksort;
use function sprintf;

final class Codec implements CodecInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ModifierCollectionInterface $modifierCollection,
    ) {}

    public function encode(ValueInterface $value): string
    {
        $parameters = $value->getValue();

        if (!is_array($parameters)) {
            throw new InvalidArgumentException(sprintf(
                'Can not decode value of type %s, the value must be array<string, string|numeric|bool>.',
                gettype($parameters),
            ));
        }

        if (empty($parameters)) {
            throw new InvalidArgumentException('Value can not be an empty array.');
        }

        /** @var array<string, string|numeric|bool> $parameters */
        $assigner = $this->config[Config::MODIFIER_ASSIGNER];
        $separator = $this->config[Config::MODIFIER_SEPARATOR];
        $result = [];
        assert(\is_string($assigner) && \is_string($separator));

        ksort($parameters);

        foreach ($parameters as $k => $v) {
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

    public function decode(ValueInterface $value): array
    {
        $path = $value->getValue();

        if (!is_string($path) && !$path instanceof Stringable) {
            throw new InvalidArgumentException(sprintf(
                'Can not decode value of type %s, the value must be string or Stringable object.',
                gettype($path),
            ));
        }

        $path = (string) $path;

        if (empty($path)) {
            throw new InvalidArgumentException('Value can not be an empty string.');
        }

        $parameters = [];
        $assigner = $this->config[Config::MODIFIER_ASSIGNER];
        $separator = $this->config[Config::MODIFIER_SEPARATOR];
        assert(\is_string($assigner) && \is_string($separator));

        $assigner = empty($assigner) ? ':' : $assigner;
        $separator = empty($separator) ? ',' : $separator;

        foreach (explode($separator, $path) as $modifier) {
            $modifier = explode($assigner, $modifier);
            $count = count($modifier);

            if (2 < $count) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" has an invalid format.',
                    $path,
                    implode($assigner, $modifier),
                ));
            }

            $modifierObject = $this->modifierCollection->getByAlias($modifier[0]);

            if (1 === $count && $modifierObject instanceof ParsableModifierInterface) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" must have a value.',
                    $path,
                    $modifierObject->getAlias(),
                ));
            }

            if (2 === $count && !$modifierObject instanceof ParsableModifierInterface) {
                throw new InvalidArgumentException(sprintf(
                    'An invalid path "%s" passed, the modifier "%s" can not have a value.',
                    $path,
                    $modifierObject->getAlias(),
                ));
            }

            $parameters[$modifierObject->getAlias()] = 2 === $count ? $modifier[1] : true;
        }

        return $parameters;
    }
}
