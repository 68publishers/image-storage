<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class WidthTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        Assert::same(Width::class, (new Width())->getName());
    }

    public function testAliasShouldBeReturned(): void
    {
        Assert::same('w', (new Width())->getAlias());
    }

    public function testValidValuesShouldBeParsed(): void
    {
        $width = new Width();

        Assert::same(100, $width->parseValue('100'));
        Assert::same(100, $width->parseValue('100.0'));
        Assert::same(100, $width->parseValue('100.1'));
    }

    public function testExceptionShouldBeThrownWhenParsedValueIsNotNumeric(): void
    {
        $width = new Width();

        Assert::exception(
            static fn () => $width->parseValue('test'),
            ModifierException::class,
            'Width must be a numeric value.',
        );
    }
}

(new WidthTest())->run();
