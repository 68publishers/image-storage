<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use function array_map;
use function array_unshift;
use function implode;
use function in_array;
use function number_format;
use function sprintf;

final class XDescriptor implements DescriptorInterface
{
    /** @var array<float> */
    private array $pixelDensities;

    /**
     * @param int|float|string ...$pixelDensities
     */
    public function __construct(...$pixelDensities)
    {
        $pixelDensities = array_map('floatval', $pixelDensities);

        if (!in_array(1.0, $pixelDensities, true)) {
            array_unshift($pixelDensities, 1.0);
        }

        $this->pixelDensities = $pixelDensities;
    }

    public static function default(): self
    {
        return new self(1, 2, 3);
    }

    public function validateModifierValue(
        mixed $value,
        mixed $default,
    ): float {
        if (true === $value) {
            if (!is_numeric($default) && [] !== $this->pixelDensities) {
                return $this->pixelDensities[0];
            }

            $value = $default;
        }

        if (is_numeric($value) && in_array((float) $value, $this->pixelDensities, true)) {
            return (float) $value;
        }

        throw new InvalidArgumentException(
            message: sprintf(
                'Invalid preset value "%s" passed for descriptor %s',
                var_export($value, true),
                $this,
            ),
        );
    }

    public function expandModifier(
        ModifierCollectionInterface $modifierCollection,
        mixed $value,
    ): array {
        $pdAlias = $modifierCollection
            ->getByName(PixelDensity::class)
            ->getAlias();

        if (is_numeric($value) && in_array((float) $value, $this->pixelDensities, true)) {
            return [
                $pdAlias => (float) $value,
            ];
        }

        throw new InvalidArgumentException(
            message: sprintf(
                'Invalid preset value "%s" passed for descriptor %s',
                var_export($value, true),
                $this,
            ),
        );
    }

    public function iterateModifiers(ModifierCollectionInterface $modifierCollection): iterable
    {
        $pdAlias = $modifierCollection
            ->getByName(PixelDensity::class)
            ->getAlias();

        foreach ($this->pixelDensities as $pixelDensity) {
            yield [
                $pdAlias => $pixelDensity,
            ];
        }
    }

    public function __toString(): string
    {
        return sprintf('X(%s)', implode(',', $this->pixelDensities));
    }

    public function createSrcSet(ArgsFacade $args): SrcSet
    {
        $pdAlias = $args->getModifierAlias(PixelDensity::class);
        $modifiers = $args->getDefaultModifiers() ?? [];

        if (null === $pdAlias) {
            $link = empty($modifiers) ? '' : $args->createLink($modifiers);

            return new SrcSet(
                descriptor: 'x',
                links: '' !== $link ? [ '1.0' => $link ] : [],
                value: $link,
            );
        }

        $links = [];
        $parts = array_map(static function (float $pd) use ($args, $pdAlias, $modifiers, &$links) {
            $modifiers[$pdAlias] = $pd;
            $link = $args->createLink($modifiers);
            $formattedPd = number_format($pd, 1, '.', '');
            $links[$formattedPd] = $link;

            return sprintf(
                '%s%s',
                $link,
                1.0 === $pd ? '' : (' ' . $formattedPd . 'x'),
            );
        }, $this->pixelDensities);

        return new SrcSet(
            descriptor: 'x',
            links: $links,
            value: implode(', ', $parts),
        );
    }
}
