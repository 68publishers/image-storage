<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Applicator;

use Intervention\Image\AbstractDriver;
use Intervention\Image\Image;
use Mockery;
use Mockery\MockInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\Format;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Quality as QualityModifier;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class FormatTest extends TestCase
{
    public function testImageShouldBeFormattedWithPathInfoExtension(): void
    {
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('png');
        $modifierValues = $this->createModifierValues();
        $config = $this->createConfig();

        $image->shouldReceive('encode')
            ->once()
            ->with('png', 90)
            ->andReturn($modifiedImage);

        $applicator = new Format();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeFormattedWithImageMimeType(): void
    {
        $image = Mockery::mock(Image::class);
        $modifiedImage = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo(null);
        $modifierValues = $this->createModifierValues();
        $config = $this->createConfig();

        $image->shouldReceive('mime')
            ->once()
            ->withNoArgs()
            ->andReturn('image/png');

        $image->shouldReceive('encode')
            ->once()
            ->with('png', 90)
            ->andReturn($modifiedImage);

        $applicator = new Format();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeFormattedWithDefaultExtension(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo(null);
        $modifierValues = $this->createModifierValues();
        $config = $this->createConfig();

        $image->shouldReceive('mime')
            ->once()
            ->withNoArgs()
            ->andReturn('image/unsupported');

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, false);

        $modifiedImage->shouldReceive('encode')
            ->once()
            ->with('jpg', 90)
            ->andReturn($modifiedImage);

        $applicator = new Format();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeFormattedIfPathInfoExtensionIsJpg(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('jpg');
        $modifierValues = $this->createModifierValues();
        $config = $this->createConfig();

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, false);

        $modifiedImage->shouldReceive('encode')
            ->once()
            ->with('jpg', 90)
            ->andReturn($modifiedImage);

        $applicator = new Format();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    public function testImageShouldBeFormattedIfPathInfoExtensionIsPjpg(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('pjpg');
        $modifierValues = $this->createModifierValues();
        $config = $this->createConfig();

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, true);

        $modifiedImage->shouldReceive('encode')
            ->once()
            ->with('jpg', 90)
            ->andReturn($modifiedImage);

        $applicator = new Format();

        Assert::same($modifiedImage, $applicator->apply($image, $pathInfo, $modifierValues, $config));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createConfig(): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::ENCODE_QUALITY)
            ->andReturn(90);

        return $config;
    }

    private function createModifierValues(): ModifierValues
    {
        $modifierValues = Mockery::mock(ModifierValues::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(QualityModifier::class, 90)
            ->andReturn(90);

        return $modifierValues;
    }

    private function createPathInfo(?string $extension): PathInfoInterface
    {
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getExtension')
            ->once()
            ->withNoArgs()
            ->andReturn($extension);

        return $pathInfo;
    }

    private function setupJpgExpectationsOnImage(Image|MockInterface $image, bool $jppg): Image|MockInterface
    {
        $driver = Mockery::mock(AbstractDriver::class);
        $newImage = Mockery::mock(Image::class);

        $image->shouldReceive('width')
            ->once()
            ->withNoArgs()
            ->andReturn(100);

        $image->shouldReceive('height')
            ->once()
            ->withNoArgs()
            ->andReturn(200);

        $image->shouldReceive('getDriver')
            ->once()
            ->withNoArgs()
            ->andReturn($driver);

        $driver->shouldReceive('newImage')
            ->once()
            ->with(100, 200, '#fff')
            ->andReturn($newImage);

        $newImage->shouldReceive('insert')
            ->once()
            ->with($image, 'top-left', 0, 0)
            ->andReturnSelf();

        if ($jppg) {
            $newImage->shouldReceive('interlace')
                ->once()
                ->withNoArgs()
                ->andReturnSelf();
        }

        return $newImage;
    }
}

(new FormatTest())->run();
