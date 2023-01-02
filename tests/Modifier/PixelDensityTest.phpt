<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

require __DIR__ . '/../bootstrap.php';

final class PixelDensityTest extends TestCase
{
	public function testNameShouldBeReturned(): void
	{
		Assert::same(PixelDensity::class, (new PixelDensity())->getName());
	}

	public function testAliasShouldBeReturned(): void
	{
		Assert::same('pd', (new PixelDensity())->getAlias());
	}

	public function testValidValuesShouldBeParsed(): void
	{
		$width = new PixelDensity();

		Assert::same(1.0, $width->parseValue('1'));
		Assert::same(1.0, $width->parseValue('1.0'));
		Assert::same(2.5, $width->parseValue('2.5'));
		Assert::same(8.0, $width->parseValue('8'));
	}

	public function testExceptionShouldBeThrownWhenParsedValueIsNotNumeric(): void
	{
		$width = new PixelDensity();

		Assert::exception(
			static fn () => $width->parseValue('test'),
			ModifierException::class,
			'Pixel density must be a numeric value.'
		);
	}

	public function testExceptionShouldBeThrownWhenParsedValueLessThan1(): void
	{
		$width = new PixelDensity();

		Assert::exception(
			static fn () => $width->parseValue('0'),
			ModifierException::class,
			'Pixel density 0.0 is not valid, the value must be between 1 and 8.'
		);
	}

	public function testExceptionShouldBeThrownWhenParsedValueGreaterThan100(): void
	{
		$width = new PixelDensity();

		Assert::exception(
			static fn () => $width->parseValue('8.5'),
			ModifierException::class,
			'Pixel density 8.5 is not valid, the value must be between 1 and 8.'
		);
	}
}

(new PixelDensityTest())->run();
