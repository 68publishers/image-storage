<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Facade;

use CLosure;
use Intervention\Image\Image;
use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifyResult;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\Preset;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface;
use Tester\Assert;
use Tester\TestCase;
use TypeError;
use function call_user_func;

require __DIR__ . '/../../bootstrap.php';

final class ModifierFacadeTest extends TestCase
{
    public function testExceptionShouldBeThrownIfInvalidModifierPassed(): void
    {
        $facade = $this->createModifierFacade();

        Assert::exception(
            static fn () => $facade->setModifiers(['test']),
            InvalidArgumentException::class,
            'The argument passed into the method SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade::setModifiers() must be an array of SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface.',
        );
    }

    public function testModifiersShouldBeSet(): void
    {
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $modifiers = [
            Mockery::mock(ModifierInterface::class),
            Mockery::mock(ModifierInterface::class),
            Mockery::mock(ModifierInterface::class),
        ];
        $addedModifiers = [];

        $modifierCollection->shouldReceive('add')
            ->times(3)
            ->with(Mockery::type(ModifierInterface::class))
            ->andReturnUsing(static function (ModifierInterface $modifier) use (&$addedModifiers) {
                $addedModifiers[] = $modifier;

                return null;
            });

        $facade = $this->createModifierFacade(modifierCollection: $modifierCollection);

        $facade->setModifiers($modifiers);

        Assert::same($modifiers, $addedModifiers);
    }

    public function testExceptionShouldBeThrownIfInvalidPresetPassed(): void
    {
        $facade = $this->createModifierFacade();

        Assert::exception(
            static fn () => $facade->setPresets(['test']),
            TypeError::class,
        );
    }

    public function testPresetsShouldBeSet(): void
    {
        $presetCollection = Mockery::mock(PresetCollectionInterface::class);
        $presets = [
            'a' => new Preset(['w' => 100], null, null),
            'b' => new Preset(['w' => 150, 'f' => 'stretch'], null, null),
            'c' => new Preset(['w' => 150, 'ar' => '16x9'], null, null),
        ];
        $addedPresets = [];

        $presetCollection->shouldReceive('add')
            ->times(3)
            ->with(Mockery::type('string'), Mockery::type(Preset::class))
            ->andReturnUsing(static function (string $name, Preset $preset) use (&$addedPresets) {
                $addedPresets[$name] = $preset;

                return null;
            });

        $facade = $this->createModifierFacade(presetCollection: $presetCollection);

        $facade->setPresets($presets);

        Assert::same($presets, $addedPresets);
    }

    public function testExceptionShouldBeThrownIfInvalidApplicatorPassed(): void
    {
        $facade = $this->createModifierFacade();

        Assert::exception(
            static fn () => $facade->setApplicators(['test']),
            InvalidArgumentException::class,
            'The argument passed into the method SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade::setApplicators() must be an array of SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface.',
        );
    }

    public function testApplicatorsShouldBeSet(): void
    {
        $applicators = [
            Mockery::mock(ModifierApplicatorInterface::class),
            Mockery::mock(ModifierApplicatorInterface::class),
            Mockery::mock(ModifierApplicatorInterface::class),
        ];

        $facade = $this->createModifierFacade();

        $facade->setApplicators($applicators);

        call_user_func(CLosure::bind(
            static function () use ($facade, $applicators): void {
                Assert::same($applicators, $facade->applicators);
            },
            null,
            ModifierFacade::class,
        ));
    }

    public function testExceptionShouldBeThrownIfInvalidValidatorPassed(): void
    {
        $facade = $this->createModifierFacade();

        Assert::exception(
            static fn () => $facade->setValidators(['test']),
            InvalidArgumentException::class,
            'The argument passed into the method SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacade::setValidators() must be an array of SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface.',
        );
    }

    public function testValidatorsShouldBeSet(): void
    {
        $validators = [
            Mockery::mock(ValidatorInterface::class),
            Mockery::mock(ValidatorInterface::class),
            Mockery::mock(ValidatorInterface::class),
        ];

        $facade = $this->createModifierFacade();

        $facade->setValidators($validators);

        call_user_func(CLosure::bind(
            static function () use ($facade, $validators): void {
                Assert::same($validators, $facade->validators);
            },
            null,
            ModifierFacade::class,
        ));
    }

    public function testModifierCollectionShouldBeReturned(): void
    {
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $facade = $this->createModifierFacade(modifierCollection: $modifierCollection);

        Assert::same($modifierCollection, $facade->getModifierCollection());
    }

    public function testCodecShouldBeReturned(): void
    {
        $codec = Mockery::mock(CodecInterface::class);
        $facade = $this->createModifierFacade(codec: $codec);

        Assert::same($codec, $facade->getCodec());
    }

    public function testExceptionShouldBeThrownIfImageIsModifiedWithEmptyModifiers(): void
    {
        $facade = $this->createModifierFacade();

        Assert::exception(
            static fn () => $facade->modifyImage(Mockery::mock(Image::class), Mockery::mock(PathInfoInterface::class), []),
            InvalidArgumentException::class,
            'Unable to modify the image, modifiers are empty.',
        );
    }

    public function testImageShouldBeModifiedWithArrayModifiers(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $validator = Mockery::mock(ValidatorInterface::class);
        $applicator = Mockery::mock(ModifierApplicatorInterface::class);

        $modifierValues = Mockery::mock(ModifierValues::class);
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiers = ['w' => 100, 'h' => 200];

        $modifierCollection->shouldReceive('parseValues')
            ->once()
            ->with($modifiers)
            ->andReturn($modifierValues);

        $validator->shouldReceive('validate')
            ->once()
            ->with($modifierValues, $config);

        $applicator->shouldReceive('apply')
            ->once()
            ->with($image, $pathInfo, $modifierValues, $config)
            ->andReturn([ModifierApplicatorInterface::OutImage => $image]);

        $facade = $this->createModifierFacade(config: $config, modifierCollection: $modifierCollection);

        $facade->setApplicators([$applicator]);
        $facade->setValidators([$validator]);

        Assert::equal(
            new ModifyResult(
                image: $image,
                modified: true,
                encodeFormat: null,
                encodeQuality: null,
            ),
            $facade->modifyImage($image, $pathInfo, $modifiers),
        );
    }

    public function testImageShouldNotBeModifiedWithArrayModifiers(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $validator = Mockery::mock(ValidatorInterface::class);
        $applicator = Mockery::mock(ModifierApplicatorInterface::class);

        $modifierValues = Mockery::mock(ModifierValues::class);
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiers = ['w' => 100, 'h' => 200];

        $modifierCollection->shouldReceive('parseValues')
            ->once()
            ->with($modifiers)
            ->andReturn($modifierValues);

        $validator->shouldReceive('validate')
            ->once()
            ->with($modifierValues, $config);

        $applicator->shouldReceive('apply')
            ->once()
            ->with($image, $pathInfo, $modifierValues, $config)
            ->andReturn([]);

        $facade = $this->createModifierFacade(config: $config, modifierCollection: $modifierCollection);

        $facade->setApplicators([$applicator]);
        $facade->setValidators([$validator]);

        Assert::equal(
            new ModifyResult(
                image: $image,
                modified: false,
                encodeFormat: null,
                encodeQuality: null,
            ),
            $facade->modifyImage($image, $pathInfo, $modifiers),
        );
    }

    public function testImageShouldBeModifiedWithPresetModifiers(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $codec = Mockery::mock(CodecInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $validator = Mockery::mock(ValidatorInterface::class);
        $applicator = Mockery::mock(ModifierApplicatorInterface::class);

        $modifierValues = Mockery::mock(ModifierValues::class);
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiers = ['w' => 100, 'h' => 200];
        $preset = 'preset';

        $codec->shouldReceive('expandModifiers')
            ->once()
            ->with($preset)
            ->andReturn($modifiers);

        $modifierCollection->shouldReceive('parseValues')
            ->once()
            ->with($modifiers)
            ->andReturn($modifierValues);

        $validator->shouldReceive('validate')
            ->once()
            ->with($modifierValues, $config);

        $applicator->shouldReceive('apply')
            ->once()
            ->with($image, $pathInfo, $modifierValues, $config)
            ->andReturn([ModifierApplicatorInterface::OutImage => $image]);

        $facade = $this->createModifierFacade(config: $config, codec: $codec, modifierCollection: $modifierCollection);

        $facade->setApplicators([$applicator]);
        $facade->setValidators([$validator]);

        Assert::equal(
            new ModifyResult(
                image: $image,
                modified: true,
                encodeFormat: null,
                encodeQuality: null,
            ),
            $facade->modifyImage($image, $pathInfo, $preset),
        );
    }

    public function testImageShouldNotBeModifiedWithPresetModifiers(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $codec = Mockery::mock(CodecInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);
        $validator = Mockery::mock(ValidatorInterface::class);
        $applicator = Mockery::mock(ModifierApplicatorInterface::class);

        $modifierValues = Mockery::mock(ModifierValues::class);
        $image = Mockery::mock(Image::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiers = ['w' => 100, 'h' => 200];
        $preset = 'preset';

        $codec->shouldReceive('expandModifiers')
            ->once()
            ->with($preset)
            ->andReturn($modifiers);

        $modifierCollection->shouldReceive('parseValues')
            ->once()
            ->with($modifiers)
            ->andReturn($modifierValues);

        $validator->shouldReceive('validate')
            ->once()
            ->with($modifierValues, $config);

        $applicator->shouldReceive('apply')
            ->once()
            ->with($image, $pathInfo, $modifierValues, $config)
            ->andReturn([]);

        $facade = $this->createModifierFacade(config: $config, codec: $codec, modifierCollection: $modifierCollection);

        $facade->setApplicators([$applicator]);
        $facade->setValidators([$validator]);

        Assert::equal(
            new ModifyResult(
                image: $image,
                modified: false,
                encodeFormat: null,
                encodeQuality: null,
            ),
            $facade->modifyImage($image, $pathInfo, $preset),
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createModifierFacade(
        ?ConfigInterface $config = null,
        ?CodecInterface $codec = null,
        ?PresetCollectionInterface $presetCollection = null,
        ?ModifierCollectionInterface $modifierCollection = null,
    ): ModifierFacade {
        return new ModifierFacade(
            $config ?? Mockery::mock(ConfigInterface::class),
            $codec ?? Mockery::mock(CodecInterface::class),
            $presetCollection ?? Mockery::mock(PresetCollectionInterface::class),
            $modifierCollection ?? Mockery::mock(ModifierCollectionInterface::class),
        );
    }
}

(new ModifierFacadeTest())->run();
