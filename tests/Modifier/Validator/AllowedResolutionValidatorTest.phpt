<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Validator;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\Modifier\Height;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedResolutionValidator;

require __DIR__ . '/../../bootstrap.php';

final class AllowedResolutionValidatorTest extends TestCase
{
	/**
	 * @dataProvider getValidValuesData
	 */
	public function testDimensionsShouldBeValid(?int $width, ?int $height, ?array $allowed): void
	{
		$config = Mockery::mock(ConfigInterface::class);
		$values = Mockery::mock(ModifierValues::class);

		$config->shouldReceive('offsetGet')
			->once()
			->with(Config::ALLOWED_RESOLUTIONS)
			->andReturn($allowed);

		if (!empty($allowed)) {
			$values->shouldReceive('getOptional')
				->once()
				->with(Width::class)
				->andReturn($width);

			$values->shouldReceive('getOptional')
				->once()
				->with(Height::class)
				->andReturn($height);
		}

		$validator = new AllowedResolutionValidator();

		Assert::noError(static fn () => $validator->validate($values, $config));
	}

	/**
	 * @dataProvider getInvalidValuesData
	 */
	public function testDimensionsShouldBeInvalid(?int $width, ?int $height, array $allowed): void
	{
		$config = Mockery::mock(ConfigInterface::class);
		$values = Mockery::mock(ModifierValues::class);

		$config->shouldReceive('offsetGet')
			->once()
			->with(Config::ALLOWED_RESOLUTIONS)
			->andReturn($allowed);

		$values->shouldReceive('getOptional')
			->once()
			->with(Width::class)
			->andReturn($width);

		$values->shouldReceive('getOptional')
			->once()
			->with(Height::class)
			->andReturn($height);

		$validator = new AllowedResolutionValidator();

		Assert::exception(
			static fn () => $validator->validate($values, $config),
			ModifierException::class,
			"Invalid combination of width and height modifiers, {$width}x{$height} is not supported."
		);
	}

	public function getValidValuesData(): array
	{
		return [
			[null, null, null],
			[null, null, []],
			[null, null, ['100x']],
			[100, null, ['100x']],
			[100, 200, ['100x']],
			[null, 200, ['x200']],
			[100, 200, ['x200']],
			[100, 200, ['100x200']],
		 ];
	}

	public function getInvalidValuesData(): array
	{
		return [
			[100, null, ['150x']],
			[100, 100, ['150x']],
			[null, 100, ['x150']],
			[100, 100, ['x150']],
			[100, 100, ['150x150']],
		];
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}
}

(new AllowedResolutionValidatorTest())->run();
