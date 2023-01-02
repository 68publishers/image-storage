<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Responsive\Descriptor;

use Closure;
use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\XDescriptor;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class XDescriptorTest extends TestCase
{
	public function testDefaultDescriptorShouldBeCreated(): void
	{
		$this->assertPixelDensities(XDescriptor::default(), [1.0, 2.0, 3.0]);
	}

	public function testDescriptorShouldBeCreatedViaConstructor(): void
	{
		$this->assertPixelDensities(new XDescriptor(), [1.0]);
		$this->assertPixelDensities(new XDescriptor(1.0, 2.0, 2.5), [1.0, 2.0, 2.5]);
		$this->assertPixelDensities(new XDescriptor(1, 2, 3), [1.0, 2.0, 3.0]);
		$this->assertPixelDensities(new XDescriptor('1', '2.5', '3.0'), [1.0, 2.5, 3.0]);
	}

	public function testDescriptorShouldBeConvertedIntoString(): void
	{
		Assert::same('X(1)', (string) new XDescriptor());
		Assert::same('X(1,2,2.5)', (string) new XDescriptor(1.0, 2.0, 2.5));
		Assert::same('X(1,2,3)', (string) new XDescriptor(1.0, 2.0, 3.0));
	}

	public function testSrcSetShouldBeEmptyStringIfPixelDensityModifierNotFoundAndDefaultModifiersAreNull(): void
	{
		$argsFacade = Mockery::mock(ArgsFacade::class);

		$argsFacade->shouldReceive('getModifierAlias')
			->once()
			->with(PixelDensity::class)
			->andReturn(null);

		$argsFacade->shouldReceive('getDefaultModifiers')
			->once()
			->withNoArgs()
			->andReturn(null);

		$descriptor = new XDescriptor(1, 2, 2.5);

		Assert::same('', $descriptor->createSrcSet($argsFacade));
	}

	public function testSrcSetShouldBeEmptyStringIfPixelDensityModifierNotFoundAndDefaultModifiersAreEmptyArray(): void
	{
		$argsFacade = Mockery::mock(ArgsFacade::class);

		$argsFacade->shouldReceive('getModifierAlias')
			->once()
			->with(PixelDensity::class)
			->andReturn(null);

		$argsFacade->shouldReceive('getDefaultModifiers')
			->once()
			->withNoArgs()
			->andReturn([]);

		$descriptor = new XDescriptor(1, 2, 2.5);

		Assert::same('', $descriptor->createSrcSet($argsFacade));
	}

	public function testSrcSetShouldBeSingleLinkIfWidthModifierNotFound(): void
	{
		$argsFacade = Mockery::mock(ArgsFacade::class);

		$argsFacade->shouldReceive('getModifierAlias')
			->once()
			->with(PixelDensity::class)
			->andReturn(null);

		$argsFacade->shouldReceive('getDefaultModifiers')
			->once()
			->withNoArgs()
			->andReturn(['h' => 100]);

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['h' => 100])
			->andReturn('var/www/h:100/file.png');

		$descriptor = new XDescriptor(1, 2, 2.5);

		Assert::same('var/www/h:100/file.png', $descriptor->createSrcSet($argsFacade));
	}

	public function testSrcSetShouldContainMultipleLinksWithoutDefaultModifiers(): void
	{
		$argsFacade = Mockery::mock(ArgsFacade::class);

		$argsFacade->shouldReceive('getModifierAlias')
			->once()
			->with(PixelDensity::class)
			->andReturn('pd');

		$argsFacade->shouldReceive('getDefaultModifiers')
			->once()
			->withNoArgs()
			->andReturn(null);

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['pd' => 1.0])
			->andReturn('var/www/pd:1/file.png');

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['pd' => 2.0])
			->andReturn('var/www/pd:2/file.png');

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['pd' => 2.5])
			->andReturn('var/www/pd:2.5/file.png');

		$descriptor = new XDescriptor(1, 2, 2.5);

		Assert::same('var/www/pd:1/file.png, var/www/pd:2/file.png 2.0x, var/www/pd:2.5/file.png 2.5x', $descriptor->createSrcSet($argsFacade));
	}

	public function testSrcSetShouldContainMultipleLinksWithDefaultModifiers(): void
	{
		$argsFacade = Mockery::mock(ArgsFacade::class);

		$argsFacade->shouldReceive('getModifierAlias')
			->once()
			->with(PixelDensity::class)
			->andReturn('pd');

		$argsFacade->shouldReceive('getDefaultModifiers')
			->once()
			->withNoArgs()
			->andReturn(['h' => 100]);

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['h' => 100, 'pd' => 1.0])
			->andReturn('var/www/h:100,pd:1/file.png');

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['h' => 100, 'pd' => 2.0])
			->andReturn('var/www/h:100,pd:2/file.png');

		$argsFacade->shouldReceive('createLink')
			->once()
			->with(['h' => 100, 'pd' => 2.5])
			->andReturn('var/www/h:100,pd:2.5/file.png');

		$descriptor = new XDescriptor(1, 2, 2.5);

		Assert::same('var/www/h:100,pd:1/file.png, var/www/h:100,pd:2/file.png 2.0x, var/www/h:100,pd:2.5/file.png 2.5x', $descriptor->createSrcSet($argsFacade));
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function assertPixelDensities(XDescriptor $descriptor, array $pixelDensities): void
	{
		call_user_func(Closure::bind(
			static function () use ($descriptor, $pixelDensities): void {
				Assert::same($pixelDensities, $descriptor->pixelDensities);
			},
			null,
			XDescriptor::class
		));
	}
}

(new XDescriptorTest())->run();
