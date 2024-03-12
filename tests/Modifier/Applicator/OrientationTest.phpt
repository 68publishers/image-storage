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
    public function testImageShouldNotBeModifiedIfValueIsNotStringOrNumber(): void
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

        Assert::same($image, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeAutomaticallyOrientated(): void
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

        $image->shouldReceive('orientate')
            ->once()
            ->withNoArgs()
            ->andReturn($modifiedImage);

        $applicator = new Orientation();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
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
            ->andReturn(-90);

        $image->shouldReceive('rotate')
            ->once()
            ->with(-90.0)
            ->andReturn($modifiedImage);

        $applicator = new Orientation();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new OrientationTest())->run();
