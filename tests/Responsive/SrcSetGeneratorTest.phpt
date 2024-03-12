<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Responsive;

use Closure;
use Mockery;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator;
use Tester\Assert;
use Tester\TestCase;
use function call_user_func;

require __DIR__ . '/../bootstrap.php';

final class SrcSetGeneratorTest extends TestCase
{
    public function testSrcSetShouldBeGeneratedAndCachedWithNullDefaultModifiers(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiedPathInfo = Mockery::mock(PathInfoInterface::class);

        $descriptor->shouldReceive('__toString')
            ->times(2)
            ->withNoArgs()
            ->andReturn('TEST()');

        $pathInfo->shouldReceive('getModifiers')
            ->times(3) # 2x in SrcSetGenerator, 1x in ArgsFacade
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('withModifiers')
            ->times(2)
            ->with(['original' => true])
            ->andReturn($modifiedPathInfo);

        $modifiedPathInfo->shouldReceive('__toString')
            ->times(2)
            ->withNoArgs()
            ->andReturn('var/www/original/file.png');

        $descriptor->shouldReceive('createSrcSet')
            ->times(1)
            ->with(Mockery::type(ArgsFacade::class))
            ->andReturnUsing(function (ArgsFacade $facade) use ($linkGenerator, $modifierFacade, $pathInfo): string {
                $this->assertFacadeProperties($facade, $linkGenerator, $modifierFacade, $pathInfo);

                return 'srcset';
            });

        $generator = new SrcSetGenerator($linkGenerator, $modifierFacade);

        $this->assertCache($generator, []);

        Assert::same('srcset', $generator->generate($descriptor, $pathInfo));

        $this->assertCache($generator, [
            'TEST()::var/www/original/file.png' => 'srcset',
        ]);

        Assert::same('srcset', $generator->generate($descriptor, $pathInfo));

        $this->assertCache($generator, [
            'TEST()::var/www/original/file.png' => 'srcset',
        ]);
    }

    public function testSrcSetShouldBeGeneratedAndCachedWithDefaultModifiers(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $descriptor = Mockery::mock(DescriptorInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $descriptor->shouldReceive('__toString')
            ->times(2)
            ->withNoArgs()
            ->andReturn('TEST()');

        $pathInfo->shouldReceive('getModifiers')
            ->times(3) # 2x in SrcSetGenerator, 1x in ArgsFacade
            ->withNoArgs()
            ->andReturn(['h' => 100]);

        $pathInfo->shouldReceive('__toString')
            ->times(2)
            ->withNoArgs()
            ->andReturn('var/www/h:100/file.png');

        $descriptor->shouldReceive('createSrcSet')
            ->times(1)
            ->with(Mockery::type(ArgsFacade::class))
            ->andReturnUsing(function (ArgsFacade $facade) use ($linkGenerator, $modifierFacade, $pathInfo): string {
                $this->assertFacadeProperties($facade, $linkGenerator, $modifierFacade, $pathInfo);

                return 'srcset';
            });

        $generator = new SrcSetGenerator($linkGenerator, $modifierFacade);

        $this->assertCache($generator, []);

        Assert::same('srcset', $generator->generate($descriptor, $pathInfo));

        $this->assertCache($generator, [
            'TEST()::var/www/h:100/file.png' => 'srcset',
        ]);

        Assert::same('srcset', $generator->generate($descriptor, $pathInfo));

        $this->assertCache($generator, [
            'TEST()::var/www/h:100/file.png' => 'srcset',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function assertFacadeProperties(ArgsFacade $facade, LinkGeneratorInterface $linkGenerator, ModifierFacadeInterface $modifierFacade, PathInfoInterface $pathInfo): void
    {
        call_user_func(Closure::bind(
            static function () use ($facade, $linkGenerator, $modifierFacade, $pathInfo): void {
                Assert::same($facade->linkGenerator, $linkGenerator);
                Assert::same($facade->modifierFacade, $modifierFacade);
                Assert::same($facade->pathInfo, $pathInfo);
            },
            null,
            ArgsFacade::class,
        ));
    }

    private function assertCache(SrcSetGenerator $generator, array $expectedCache): void
    {
        call_user_func(Closure::bind(
            static function () use ($generator, $expectedCache): void {
                Assert::same($expectedCache, $generator->results);
            },
            null,
            SrcSetGenerator::class,
        ));
    }
}

(new SrcSetGeneratorTest())->run();
