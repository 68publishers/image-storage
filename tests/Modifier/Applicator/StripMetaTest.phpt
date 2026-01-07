<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Applicator;

use Imagick;
use Intervention\Image\Image;
use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\StripMeta;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class StripMetaTest extends TestCase
{
    public function testEmptyGeneratorShouldBeReturnedIfStripMetaIsNotSet(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with('__stripMeta', false)
            ->andReturn(false);

        $applicator = new StripMeta();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function testEmptyGeneratorShouldBeReturnedIfCoreIsNotImagick(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);
        $core = new stdClass();

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with('__stripMeta', false)
            ->andReturn(true);

        $image->shouldReceive('getCore')
            ->once()
            ->withNoArgs()
            ->andReturn($core);

        $applicator = new StripMeta();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function testMetadataShouldBeStrippedButIccProfileShouldBeKept(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);
        $core = Mockery::mock(Imagick::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with('__stripMeta', false)
            ->andReturn(true);

        $image->shouldReceive('getCore')
            ->once()
            ->withNoArgs()
            ->andReturn($core);

        $core->shouldReceive('getImageProfiles')
            ->once()
            ->with('icc')
            ->andReturn(['icc' => 'icc_profile_data']);

        $core->shouldReceive('stripImage')
            ->once()
            ->withNoArgs();

        $core->shouldReceive('profileImage')
            ->once()
            ->with('icc', 'icc_profile_data');

        $applicator = new StripMeta();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function testMetadataShouldBeStrippedWithoutIccProfile(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierValues = Mockery::mock(ModifierValues::class);
        $config = Mockery::mock(ConfigInterface::class);
        $core = Mockery::mock(Imagick::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with('__stripMeta', false)
            ->andReturn(true);

        $image->shouldReceive('getCore')
            ->once()
            ->withNoArgs()
            ->andReturn($core);

        $core->shouldReceive('getImageProfiles')
            ->once()
            ->with('icc')
            ->andReturn([]);

        $core->shouldReceive('stripImage')
            ->once()
            ->withNoArgs();

        $applicator = new StripMeta();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new StripMetaTest())->run();
