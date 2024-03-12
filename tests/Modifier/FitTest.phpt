<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Fit;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class FitTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        Assert::same(Fit::class, (new Fit())->getName());
    }

    public function testAliasShouldBeReturned(): void
    {
        Assert::same('f', (new Fit())->getAlias());
    }

    public function testValidValuesShouldBeParsed(): void
    {
        $width = new Fit();

        Assert::same('fill', $width->parseValue('fill'));
        Assert::same('stretch', $width->parseValue('stretch'));
        Assert::same('contain', $width->parseValue('contain'));
        Assert::same('crop-center', $width->parseValue('crop-center'));
        Assert::same('crop-left', $width->parseValue('crop-left'));
        Assert::same('crop-right', $width->parseValue('crop-right'));
        Assert::same('crop-top', $width->parseValue('crop-top'));
        Assert::same('crop-top-left', $width->parseValue('crop-top-left'));
        Assert::same('crop-top-right', $width->parseValue('crop-top-right'));
        Assert::same('crop-bottom', $width->parseValue('crop-bottom'));
        Assert::same('crop-bottom-left', $width->parseValue('crop-bottom-left'));
        Assert::same('crop-bottom-right', $width->parseValue('crop-bottom-right'));
    }

    public function testExceptionShouldBeThrownWhenParsedValueIsOutOfSet(): void
    {
        $width = new Fit();

        Assert::exception(
            static fn () => $width->parseValue('test'),
            ModifierException::class,
            'Value "test" is not a valid fit.',
        );
    }
}

(new FitTest())->run();
