<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;

require __DIR__ . '/../bootstrap.php';

final class QualityTest extends TestCase
{
	public function testNameShouldBeReturned(): void
	{
		Assert::same(Quality::class, (new Quality())->getName());
	}

	public function testAliasShouldBeReturned(): void
	{
		Assert::same('q', (new Quality())->getAlias());
	}

	public function testValidValuesShouldBeParsed(): void
	{
		$width = new Quality();

		Assert::same(1, $width->parseValue('1'));
		Assert::same(1, $width->parseValue('1.0'));
		Assert::same(100, $width->parseValue('100'));
		Assert::same(100, $width->parseValue('100.0'));
		Assert::same(60, $width->parseValue('60'));
	}

	public function testExceptionShouldBeThrownWhenParsedValueIsNotNumeric(): void
	{
		$width = new Quality();

		Assert::exception(
			static fn () => $width->parseValue('test'),
			ModifierException::class,
			'Quality must be an int between 1 and 100.'
		);
	}

	public function testExceptionShouldBeThrownWhenParsedValueLessThan1(): void
	{
		$width = new Quality();

		Assert::exception(
			static fn () => $width->parseValue('0'),
			ModifierException::class,
			'Quality must be an int between 1 and 100.'
		);
	}

	public function testExceptionShouldBeThrownWhenParsedValueGreaterThan100(): void
	{
		$width = new Quality();

		Assert::exception(
			static fn () => $width->parseValue('101'),
			ModifierException::class,
			'Quality must be an int between 1 and 100.'
		);
	}
}

(new QualityTest())->run();
