<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use function array_map;
use function array_unique;
use function array_values;
use function implode;
use function range;
use function sprintf;

final class WDescriptor implements DescriptorInterface
{
    /** @var array<int> */
    private array $widths;

    public function __construct(int ...$widths)
    {
        $this->widths = $widths;
    }

    public static function fromRange(int $min, int $max, int $step = 100): self
    {
        if (0 >= $min || 0 >= $max || 0 >= $step) {
            throw new InvalidArgumentException(sprintf(
                'Can not create WDescriptor from the range %d..%d with step %d.',
                $min,
                $max,
                $step,
            ));
        }

        if ($max < $min) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }

        if (($max - $min) < $step) {
            throw new InvalidArgumentException(sprintf(
                'Can not create WDescriptor from the range %d..%d with step %d. The step must not exceed the specified range.',
                $min,
                $max,
                $step,
            ));
        }

        $range = range($min, $max, $step);
        $range[] = $max;

        return new self(...array_values(array_unique($range)));
    }

    public function __toString(): string
    {
        return sprintf('W(%s)', implode(',', $this->widths));
    }

    public function createSrcSet(ArgsFacade $args): SrcSet
    {
        $wAlias = $args->getModifierAlias(Width::class);
        $modifiers = $args->getDefaultModifiers() ?? [];

        if (null === $wAlias) {
            $link = empty($modifiers) ? '' : $args->createLink($modifiers);

            return new SrcSet(
                descriptor: 'w',
                links: '' !== $link ? [ 0 => $link ] : [],
                value: $link,
            );
        }

        $links = [];
        $parts = array_map(static function (int $w) use ($args, $wAlias, $modifiers, &$links) {
            $modifiers[$wAlias] = $w;
            $link = $args->createLink($modifiers);
            $links[$w] = $link;

            return sprintf(
                '%s %dw',
                $link,
                $w,
            );
        }, $this->widths);

        return new SrcSet(
            descriptor: 'w',
            links: $links,
            value: implode(', ', $parts),
        );
    }
}
