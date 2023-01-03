<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\ImageStorageLambda;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\ParameterOverrides;

require __DIR__ . '/../../bootstrap.php';

final class ParameterOverridesTest extends TestCase
{
	public function testShouldBeExtendedWithMissingParameters(): void
	{
		$parameters = new ParameterOverrides([
			'KEY_A' => 'VALUE_A',
			'KEY_B' => [
				'VALUE_B_1',
				'VALUE_B_2',
			],
			'KEY_C' => 15,
		]);

		$extendedParameters = $parameters->withMissingParameters([
			'KEY_B' => [
				'VALUE_B_3',
			],
			'KEY_D' => true,
		]);

		Assert::notSame($parameters, $extendedParameters);
		Assert::same([
			'KEY_A' => 'VALUE_A',
			'KEY_B' => [
				'VALUE_B_1',
				'VALUE_B_2',
			],
			'KEY_C' => 15,
		], $parameters->parameters);
		Assert::same([
			'KEY_A' => 'VALUE_A',
			'KEY_B' => [
				'VALUE_B_1',
				'VALUE_B_2',
			],
			'KEY_C' => 15,
			'KEY_D' => true,
		], $extendedParameters->parameters);
	}

	public function testEmptyParametersShouldBeConvertedIntoEmptyString(): void
	{
		$parameters = new ParameterOverrides([]);

		Assert::same('', (string) $parameters);
	}

	public function testParametersShouldBeConvertedIntoString(): void
	{
		$parameters = new ParameterOverrides([
			'KEY_A' => 'VALUE_A',
			'KEY_B' => [
				'VALUE_B_1',
				'VALUE_B_2',
			],
			'KEY_C' => 15,
		]);

		Assert::same('KEY_A="VALUE_A" KEY_B="VALUE_B_1,VALUE_B_2" KEY_C="15"', (string) $parameters);
	}
}

(new ParameterOverridesTest())->run();
