<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Applicator;

use Intervention\Image\Image;
use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\Orientation;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Orientation as OrientationModifier;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class OrientationTest extends TestCase
{
    public function testNullShouldBeReturnedIfValueIsNotStringOrNumber(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(OrientationModifier::class)
            ->andReturn(null);

        $applicator = new Orientation();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    /**
     * @dataProvider provideNormalExifOrientations
     */
    public function testNullShouldBeReturnedIfImageHasNormalOrientation(int $exifOrientation): void
    {
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(OrientationModifier::class)
            ->andReturn('auto');

        $image->shouldReceive('exif')
            ->once()
            ->with('Orientation')
            ->andReturn($exifOrientation);

        $applicator = new Orientation();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    /**
     * @dataProvider provideNonNormalExifOrientations
     */
    public function testImageShouldBeAutomaticallyOrientated(int $exifOrientation): void
    {
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(OrientationModifier::class)
            ->andReturn('auto');

        $image->shouldReceive('exif')
            ->once()
            ->with('Orientation')
            ->andReturn($exifOrientation);

        $image->shouldReceive('orientate')
            ->once()
            ->withNoArgs()
            ->andReturn($modifiedImage);

        $applicator = new Orientation();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($modifiedImage, $result['image']);
    }

    public function testImageShouldBeRotated(): void
    {
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(OrientationModifier::class)
            ->andReturn('-90');

        $image->shouldReceive('rotate')
            ->once()
            ->with(-90.0)
            ->andReturn($modifiedImage);

        $applicator = new Orientation();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($modifiedImage, $result['image']);
    }

    public function provideNormalExifOrientations(): array
    {
        return [
            [0], # unknown?
            [1], # normal
        ];
    }

    public function provideNonNormalExifOrientations(): array
    {
        return [
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new OrientationTest())->run();
