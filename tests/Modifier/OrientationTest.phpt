<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Orientation;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class OrientationTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        Assert::same(Orientation::class, (new Orientation())->getName());
    }

    public function testAliasShouldBeReturned(): void
    {
        Assert::same('o', (new Orientation())->getAlias());
    }

    public function testValidValuesShouldBeParsed(): void
    {
        $width = new Orientation();

        Assert::same('auto', $width->parseValue('auto'));
        Assert::same(0, $width->parseValue('0'));
        Assert::same(90, $width->parseValue('90'));
        Assert::same(-90, $width->parseValue('-90'));
        Assert::same(180, $width->parseValue('180'));
        Assert::same(-180, $width->parseValue('-180'));
        Assert::same(270, $width->parseValue('270'));
        Assert::same(-270, $width->parseValue('-270'));
    }

    public function testExceptionShouldBeThrownWhenParsedValueIsOutOfSet(): void
    {
        $width = new Orientation();

        Assert::exception(
            static fn () => $width->parseValue('test'),
            ModifierException::class,
            'Value "test" is not a valid orientation.',
        );
    }
}

(new OrientationTest())->run();
