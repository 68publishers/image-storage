<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\AspectRatio;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

require __DIR__ . '/../bootstrap.php';

final class AspectRatioTest extends TestCase
{
	public function testNameShouldBeReturned(): void
	{
		Assert::same(AspectRatio::class, (new AspectRatio())->getName());
	}

	public function testAliasShouldBeReturned(): void
	{
		Assert::same('ar', (new AspectRatio())->getAlias());
	}

	public function testValidValuesShouldBeParsed(): void
	{
		$width = new AspectRatio();

		Assert::same([
			AspectRatio::KEY_WIDTH => 16.0,
			AspectRatio::KEY_HEIGHT => 9.0,
		], $width->parseValue('16x9'));

		Assert::same([
			AspectRatio::KEY_WIDTH => 16.0,
			AspectRatio::KEY_HEIGHT => 9.0,
		], $width->parseValue('16.0x9.0'));

		Assert::same([
			AspectRatio::KEY_WIDTH => 16.3,
			AspectRatio::KEY_HEIGHT => 9.8,
		], $width->parseValue('16.3x9.8'));
	}

	public function testExceptionShouldBeThrownWhenParsedValueHasInvalidFormat(): void
	{
		$width = new AspectRatio();

		Assert::exception(
			static fn () => $width->parseValue('16'),
			ModifierException::class,
			'Value "16" is not a valid aspect ratio.'
		);

		Assert::exception(
			static fn () => $width->parseValue('16x'),
			ModifierException::class,
			'Value "16x" is not a valid aspect ratio.'
		);

		Assert::exception(
			static fn () => $width->parseValue('x16'),
			ModifierException::class,
			'Value "x16" is not a valid aspect ratio.'
		);

		Assert::exception(
			static fn () => $width->parseValue('16x9x9'),
			ModifierException::class,
			'Value "16x9x9" is not a valid aspect ratio.'
		);

		Assert::exception(
			static fn () => $width->parseValue('testx9'),
			ModifierException::class,
			'Value "testx9" is not a valid aspect ratio.'
		);

		Assert::exception(
			static fn () => $width->parseValue('16xtest'),
			ModifierException::class,
			'Value "16xtest" is not a valid aspect ratio.'
		);
	}
}

(new AspectRatioTest())->run();
