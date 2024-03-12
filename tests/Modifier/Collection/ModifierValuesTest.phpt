<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Collection;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ModifierValuesTest extends TestCase
{
    public function testModifierValuesShouldBeCreatedAndContainsValues(): void
    {
        $values = new ModifierValues([
            'Width' => 15,
            'AspectRatio' => [
                'w' => 16.0,
                'h' => 9.0,
            ],
            'Orientation' => 'auto',
        ]);

        Assert::true($values->has('Width'));
        Assert::true($values->has('AspectRatio'));
        Assert::true($values->has('Orientation'));
        Assert::false($values->has('Quality'));

        Assert::same(15, $values->get('Width'));
        Assert::same([
            'w' => 16.0,
            'h' => 9.0,
        ], $values->get('AspectRatio'));
        Assert::same('auto', $values->get('Orientation'));

        Assert::exception(
            static fn () => $values->get('Quality'),
            InvalidArgumentException::class,
            'Missing value for the modifier Quality.',
        );

        Assert::same(15, $values->getOptional('Width'));
        Assert::same([
            'w' => 16.0,
            'h' => 9.0,
        ], $values->getOptional('AspectRatio'));
        Assert::same('auto', $values->getOptional('Orientation'));
        Assert::same(100, $values->getOptional('Quality', 100));
    }
}

(new ModifierValuesTest())->run();
