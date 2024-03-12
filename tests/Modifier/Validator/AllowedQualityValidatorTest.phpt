<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Validator;

use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\ModifierException;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierValues;
use SixtyEightPublishers\ImageStorage\Modifier\Quality;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\AllowedQualityValidator;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class AllowedQualityValidatorTest extends TestCase
{
    /**
     * @dataProvider getValidValuesData
     */
    public function testQualityShouldBeValid(?int $quality, ?array $allowed): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $values = Mockery::mock(ModifierValues::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::ALLOWED_QUALITIES)
            ->andReturn($allowed);

        if (!empty($allowed)) {
            $values->shouldReceive('getOptional')
                ->once()
                ->with(Quality::class)
                ->andReturn($quality);
        }

        $validator = new AllowedQualityValidator();

        Assert::noError(static fn () => $validator->validate($values, $config));
    }

    public function testQualityShouldBeInvalid(): void
    {
        $config = Mockery::mock(ConfigInterface::class);
        $values = Mockery::mock(ModifierValues::class);

        $config->shouldReceive('offsetGet')
            ->once()
            ->with(Config::ALLOWED_QUALITIES)
            ->andReturn([80, 90]);

        $values->shouldReceive('getOptional')
            ->once()
            ->with(Quality::class)
            ->andReturn(70);

        $validator = new AllowedQualityValidator();

        Assert::exception(
            static fn () => $validator->validate($values, $config),
            ModifierException::class,
            'Invalid quality modifier, 70 is not supported.',
        );
    }

    public function getValidValuesData(): array
    {
        return [
            [null, null],
            [null, []],
            [null, ['80', '90']],
            [90, ['80', '90']],
            [90, [80, 90]],
         ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new AllowedQualityValidatorTest())->run();
