<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class HeightTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        Assert::same(Height::class, (new Height())->getName());
    }

    public function testAliasShouldBeReturned(): void
    {
        Assert::same('h', (new Height())->getAlias());
    }

    public function testValidValuesShouldBeParsed(): void
    {
        $width = new Height();

        Assert::same(100, $width->parseValue('100'));
        Assert::same(100, $width->parseValue('100.0'));
        Assert::same(100, $width->parseValue('100.1'));
    }

    public function testExceptionShouldBeThrownWhenParsedValueIsNotNumeric(): void
    {
        $width = new Height();

        Assert::exception(
            static fn () => $width->parseValue('test'),
            ModifierException::class,
            'Height must be a numeric value.',
        );
    }
}

(new HeightTest())->run();
