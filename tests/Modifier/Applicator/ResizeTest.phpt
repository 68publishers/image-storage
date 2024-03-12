<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Applicator;

use Closure;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Mockery;
use Mockery\MockInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\Resize;
use SixtyEightPublishers\ImageStorage\Modifier\AspectRatio as AspectRatioModifier;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Fit as FitModifier;
use SixtyEightPublishers\ImageStorage\Modifier\Height as HeightModifier;
use SixtyEightPublishers\ImageStorage\Modifier\PixelDensity as PixelDensityModifier;
use SixtyEightPublishers\ImageStorage\Modifier\Width as WidthModifier;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ResizeTest extends TestCase
{
    public function testExceptionShouldBeThrownIfValuesContainAspectRatioButNotWidthAndHeight(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(null, null, $this->createAspectRatio(16, 9));

        $applicator = new Resize();

        Assert::exception(
            static fn () => $applicator->apply($image, $pathInfo, $modifierValues, $config),
            ModifierException::class,
            'The only one dimension (width or height) must be defined if an aspect ratio is used. Passed values: w=null, h=null, ar=16x9.',
        );
    }

    public function testExceptionShouldBeThrownIfValuesContainAspectRatioButAndWidthAndHeight(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(100, 200, $this->createAspectRatio(16, 9));

        $applicator = new Resize();

        Assert::exception(
            static fn () => $applicator->apply($image, $pathInfo, $modifierValues, $config),
            ModifierException::class,
            'The only one dimension (width or height) must be defined if an aspect ratio is used. Passed values: w=100, h=200, ar=16x9.',
        );
    }

    /**
     * @dataProvider getSameImageDimensionsData
     */
    public function testImageShouldNotBeModifiedIfCalculatedDimensionsAreSameAsOriginal(int $imageWidth, int $imageHeight, ?int $widthValue, ?int $heightValue, float $pd, array $aspectRatio): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues($widthValue, $heightValue, $aspectRatio, $pd);

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn($imageWidth);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn($imageHeight);

        $applicator = new Resize();

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeModifiedWithContainFit(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(width: 150, height: 300, fit: FitModifier::CONTAIN);

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $image->shouldReceive('resize')
            ->once()
            ->with(150, 300, Mockery::type(Closure::class))
            ->andReturnUsing(static function ($w, $h, Closure $callback) use ($image): Image {
                $constraint = Mockery::mock(Constraint::class);

                $constraint->shouldReceive('aspectRatio')
                    ->once()
                    ->withNoArgs()
                    ->andReturns();

                $callback($constraint);

                return $image;
            });

        $applicator = new Resize();

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeModifiedWithStretchFit(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(width: 150, height: 300, fit: FitModifier::STRETCH);

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $image->shouldReceive('resize')
            ->once()
            ->with(150, 300)
            ->andReturnSelf();

        $applicator = new Resize();

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeModifiedWithFillFit(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(width: 150, height: 300, fit: FitModifier::FILL);

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $resizeCalled = false;

        $image->shouldReceive('resize')
            ->once()
            ->with(150, 300, Mockery::type(Closure::class))
            ->andReturnUsing(static function ($w, $h, Closure $callback) use ($image, &$resizeCalled): Image {
                $constraint = Mockery::mock(Constraint::class);

                $constraint->shouldReceive('aspectRatio')
                    ->once()
                    ->withNoArgs()
                    ->andReturns();

                $constraint->shouldReceive('upsize')
                    ->once()
                    ->withNoArgs()
                    ->andReturns();

                $callback($constraint);
                $resizeCalled = true;

                return $image;
            });

        $image->shouldReceive('resizeCanvas')
            ->once()
            ->with(150, 300, 'center')
            ->andReturnUsing(static function () use ($image, &$resizeCalled): Image {
                Assert::true($resizeCalled);

                return $image;
            });

        $applicator = new Resize();

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeModifiedWithCropFit(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $config = Mockery::mock(ConfigInterface::class);
        $modifierValues = $this->createModifierValues(width: 150, height: 300, fit: 'crop-top-right');

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $image->shouldReceive('fit')
            ->once()
            ->with(150, 300, null, 'top-right')
            ->andReturnSelf();

        $applicator = new Resize();

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function getSameImageDimensionsData(): array
    {
        return [
            [100, 200, null, null, 1.0, []],
            [200, 400, 100, 200, 2.0, []],
            [100, 200, null, 200, 1.0, []],
            [100, 200, 100, null, 1.0, []],
            [100, 200, null, 200, 1.0, $this->createAspectRatio(1, 2)],
            [100, 200, 100, null, 1.0, $this->createAspectRatio(1, 2)],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createModifierValues(?int $width, ?int $height, array $aspectRatio = [], float $pixelDensity = 1.0, string $fit = FitModifier::CROP_CENTER): ModifierValues|MockInterface
    {
        $modifierValues = Mockery::mock(ModifierValues::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(WidthModifier::class)
            ->andReturn($width);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(HeightModifier::class)
            ->andReturn($height);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(AspectRatioModifier::class, [])
            ->andReturn($aspectRatio);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(PixelDensityModifier::class, 1)
            ->andReturn($pixelDensity);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(FitModifier::class, FitModifier::CROP_CENTER)
            ->andReturn($fit);

        return $modifierValues;
    }

    private function createAspectRatio(int $width, int $height): array
    {
        return [
            AspectRatioModifier::KEY_WIDTH => $width,
            AspectRatioModifier::KEY_HEIGHT => $height,
        ];
    }
}

(new ResizeTest())->run();
