<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Validator;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedPixelDensityValidator;

require __DIR__ . '/../../bootstrap.php';

final class AllowedPixelDensityValidatorTest extends TestCase
{
	/**
	 * @dataProvider getValidValuesData
	 */
	public function testPixelDensityShouldBeValid(?float $pixelDensity, ?array $allowed): void
	{
		$config = Mockery::mock(ConfigInterface::class);
		$values = Mockery::mock(ModifierValues::class);

		$config->shouldReceive('offsetGet')
			->once()
			->with(Config::ALLOWED_PIXEL_DENSITY)
			->andReturn($allowed);

		if (!empty($allowed)) {
			$values->shouldReceive('getOptional')
				->once()
				->with(PixelDensity::class)
				->andReturn($pixelDensity);
		}

		$validator = new AllowedPixelDensityValidator();

		Assert::noError(static fn () => $validator->validate($values, $config));
	}

	public function testPixelDensityShouldBeInvalid(): void
	{
		$config = Mockery::mock(ConfigInterface::class);
		$values = Mockery::mock(ModifierValues::class);

		$config->shouldReceive('offsetGet')
			->once()
			->with(Config::ALLOWED_PIXEL_DENSITY)
			->andReturn([1.0, 2.0]);

		$values->shouldReceive('getOptional')
			->once()
			->with(PixelDensity::class)
			->andReturn(2.5);

		$validator = new AllowedPixelDensityValidator();

		Assert::exception(
			static fn () => $validator->validate($values, $config),
			ModifierException::class,
			'Invalid pixel density modifier, 2.5 is not supported.'
		);
	}

	public function getValidValuesData(): array
	{
		return [
			[null, null],
			[null, []],
			[null, ['1', '2']],
			[2.0, ['1', '2']],
			[2.0, [1, 2]],
			[2.0, ['1.0', '2.0']],
			[2.0, [1.0, 2.0]],
		 ];
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}
}

(new AllowedPixelDensityValidatorTest())->run();
