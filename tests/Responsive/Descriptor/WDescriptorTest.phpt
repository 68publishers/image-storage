<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Responsive\Descriptor;

use Closure;
use Mockery;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\WDescriptor;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;
use function sprintf;

require __DIR__ . '/../../bootstrap.php';

final class WDescriptorTest extends TestCase
{
    /**
     * @dataProvider getInvalidRangeData
     */
    public function testExceptionShouldBeThrownIfInvalidRangePassed(int $min, int $max, int $step): void
    {
        Assert::exception(
            static fn () => WDescriptor::fromRange($min, $max, $step),
            InvalidArgumentException::class,
            sprintf(
                'Can not create WDescriptor from the range %d..%d with step %d.',
                $min,
                $max,
                $step,
            ),
        );
    }

    public function testExceptionShouldBeThrownIfStepExceededRange(): void
    {
        Assert::exception(
            static fn () => WDescriptor::fromRange(100, 200, 101),
            InvalidArgumentException::class,
            'Can not create WDescriptor from the range 100..200 with step 101. The step must not exceed the specified range.',
        );
    }

    public function testDescriptorShouldBeCreatedFromRange(): void
    {
        $this->assertWidths(WDescriptor::fromRange(100, 1000, 100), [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000]);
        $this->assertWidths(WDescriptor::fromRange(100, 1000, 200), [100, 300, 500, 700, 900, 1000]);
        $this->assertWidths(WDescriptor::fromRange(100, 1101, 200), [100, 300, 500, 700, 900, 1100, 1101]);

        # min and max should be swept
        $this->assertWidths(WDescriptor::fromRange(320, 100, 50), [100, 150, 200, 250, 300, 320]);
    }

    public function testDescriptorShouldBeCreatedViaConstructor(): void
    {
        $this->assertWidths(new WDescriptor(), []);
        $this->assertWidths(new WDescriptor(100), [100]);
        $this->assertWidths(new WDescriptor(100, 200, 300), [100, 200, 300]);
    }

    public function testDescriptorShouldBeConvertedIntoString(): void
    {
        Assert::same('W()', (string) new WDescriptor());
        Assert::same('W(100)', (string) new WDescriptor(100));
        Assert::same('W(100,200,300)', (string) new WDescriptor(100, 200, 300));
    }

    public function testSrcSetShouldBeEmptyIfWidthModifierNotFoundAndDefaultModifiersAreNull(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn(null);

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $descriptor = new WDescriptor(100, 200, 300);

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [],
                value: '',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function testSrcSetShouldBeEmptyIfWidthModifierNotFoundAndDefaultModifiersAreEmptyArray(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn(null);

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn([]);

        $descriptor = new WDescriptor(100, 200, 300);

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [],
                value: '',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function testSrcSetShouldBeSingleLinkIfWidthModifierNotFound(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn(null);

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['h' => 100]);

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['h' => 100])
            ->andReturn('var/www/h:100/file.png');

        $descriptor = new WDescriptor(100, 200, 300);

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [
                    0 => 'var/www/h:100/file.png',
                ],
                value: 'var/www/h:100/file.png',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function testSrcSetShouldBeEmptyIfNoWidthsDefined(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn('w');

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['h' => 100]);

        $descriptor = new WDescriptor();

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [],
                value: '',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function testSrcSetShouldContainMultipleLinksWithoutDefaultModifiers(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn('w');

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['w' => 100])
            ->andReturn('var/www/w:100/file.png');

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['w' => 200])
            ->andReturn('var/www/w:200/file.png');

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['w' => 300])
            ->andReturn('var/www/w:300/file.png');

        $descriptor = new WDescriptor(100, 200, 300);

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [
                    100 => 'var/www/w:100/file.png',
                    200 => 'var/www/w:200/file.png',
                    300 => 'var/www/w:300/file.png',
                ],
                value: 'var/www/w:100/file.png 100w, var/www/w:200/file.png 200w, var/www/w:300/file.png 300w',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function testSrcSetShouldContainMultipleLinksWithDefaultModifiers(): void
    {
        $argsFacade = Mockery::mock(ArgsFacade::class);

        $argsFacade->shouldReceive('getModifierAlias')
            ->once()
            ->with(Width::class)
            ->andReturn('w');

        $argsFacade->shouldReceive('getDefaultModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['h' => 100]);

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['h' => 100, 'w' => 100])
            ->andReturn('var/www/h:100,w:100/file.png');

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['h' => 100, 'w' => 200])
            ->andReturn('var/www/h:100,w:200/file.png');

        $argsFacade->shouldReceive('createLink')
            ->once()
            ->with(['h' => 100, 'w' => 300])
            ->andReturn('var/www/h:100,w:300/file.png');

        $descriptor = new WDescriptor(100, 200, 300);

        Assert::equal(
            new SrcSet(
                descriptor: 'w',
                links: [
                    100 => 'var/www/h:100,w:100/file.png',
                    200 => 'var/www/h:100,w:200/file.png',
                    300 => 'var/www/h:100,w:300/file.png',
                ],
                value: 'var/www/h:100,w:100/file.png 100w, var/www/h:100,w:200/file.png 200w, var/www/h:100,w:300/file.png 300w',
            ),
            $descriptor->createSrcSet($argsFacade),
        );
    }

    public function getInvalidRangeData(): array
    {
        return [
            # invalid min
            [0, 100, 10],
            [-1, 100, 10],
            # invalid max
            [100, 0, 10],
            [100, -1, 10],
            # invalid step
            [100, 200, 0],
            [100, 200, -1],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function assertWidths(WDescriptor $descriptor, array $widths): void
    {
        call_user_func(Closure::bind(
            static function () use ($descriptor, $widths): void {
                Assert::same($widths, $descriptor->widths);
            },
            null,
            WDescriptor::class,
        ));
    }
}

(new WDescriptorTest())->run();
