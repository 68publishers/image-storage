<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Applicator;

use Imagick;
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
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class FormatTest extends TestCase
{
    public function testNullShouldBeReturnedIfQualityNotSpecifiedAndPathInfoHasSameExtensionAsImage(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('png');
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/png');

        $applicator = new Format();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function testNullShouldBeReturnedIfQualityIsNotSpecifiedAndPathInfoExtensionIsNull(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo(null);
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/png');

        $applicator = new Format();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function testImageShouldBeEncodedInDefaultFormatIfPathInfoExtensionIsNullAndImageMimeTypeIsUnsupported(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo(null);
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/unsupported');

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, false);

        $applicator = new Format();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($modifiedImage, $result['image']);
        Assert::same('jpg', $result['format']);
    }

    public function testImageShouldBeEncodedIfPathInfoExtensionIsDifferentThanImageMimeType(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('webp');
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/jpeg');

        $applicator = new Format();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($image, $result['image']);
        Assert::same('webp', $result['format']);
    }

    public function testImageShouldBeEncodedIfQualityIsSpecified(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo(null);
        $modifierValues = $this->createModifierValues(75);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/png');

        $applicator = new Format();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($image, $result['image']);
        Assert::same('png', $result['format']);
        Assert::same(75, $result['quality']);
    }

    public function testImageShouldBeEncodedToJpegFromDifferentFormat(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('jpg');
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/png');

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, false);

        $applicator = new Format();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($modifiedImage, $result['image']);
        Assert::same('jpg', $result['format']);
    }

    /**
     * @dataProvider provideImageCoresForProgressiveJpegWithInvalidInterlaceScheme
     */
    public function testImageShouldBeEncodedToProgressiveJpeg(object $core): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('pjpg');
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/jpeg');

        $image->shouldReceive('getCore')
            ->withNoArgs()
            ->andReturn($core);

        $modifiedImage = $this->setupJpgExpectationsOnImage($image, true);

        $applicator = new Format();

        $result = iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config));
        Assert::same($modifiedImage, $result['image']);
        Assert::same('jpg', $result['format']);
    }

    public function testImageShouldNotBeEncodedToProgressiveJpeg(): void
    {
        $image = Mockery::mock(Image::class);
        $pathInfo = $this->createPathInfo('pjpg');
        $modifierValues = $this->createModifierValues(null);
        $config = Mockery::mock(ConfigInterface::class);
        $core = new class extends Imagick {
            public function getInterlaceScheme(): int
            {
                return Imagick::INTERLACE_JPEG;
            }
        };

        $image->shouldReceive('mime')
            ->withNoArgs()
            ->andReturn('image/jpeg');

        $image->shouldReceive('getCore')
            ->withNoArgs()
            ->andReturn($core);

        $applicator = new Format();

        Assert::same([], iterator_to_array($applicator->apply($image, $pathInfo, $modifierValues, $config)));
    }

    public function provideImageCoresForProgressiveJpegWithInvalidInterlaceScheme(): array
    {
        return [
            'Non imagick core' => [
                new stdClass(),
            ],
            'Imagick core with INTERLACE_UNDEFINED' => [
                new class extends Imagick {
                    public function getInterlaceScheme(): int
                    {
                        return Imagick::INTERLACE_UNDEFINED;
                    }
                },
            ],
            'Imagick core with INTERLACE_NO' => [
                new class extends Imagick {
                    public function getInterlaceScheme(): int
                    {
                        return Imagick::INTERLACE_NO;
                    }
                },
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createConfigForEncode(): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::ENCODE_QUALITY)
            ->andReturn(90);

        return $config;
    }

    private function createModifierValues(?int $qualityReturn): ModifierValues
    {
        $modifierValues = Mockery::mock(ModifierValues::class);

        $modifierValues->shouldReceive('getOptional')
            ->once()
            ->with(QualityModifier::class)
            ->andReturn($qualityReturn);

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
