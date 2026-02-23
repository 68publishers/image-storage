<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Responsive\Descriptor;

use Mockery;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Width;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\ArgsFacade;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ArgsFacadeTest extends TestCase
{
    public function testDefaultModifiersShouldBeNullIfPathInfoModifiersAreNull(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::null($facade->getDefaultModifiers());
    }

    public function testDefaultModifiersShouldBeArrayIfPathInfoModifiersAreArray(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(['w' => 150]);

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::same(['w' => 150], $facade->getDefaultModifiers());
    }

    public function testDefaultModifiersShouldBeArrayIfPathInfoModifiersArePreset(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $codec = Mockery::mock(CodecInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn('preset');

        $modifierFacade->shouldReceive('getCodec')
            ->once()
            ->withNoArgs()
            ->andReturn($codec);

        $codec->shouldReceive('expandModifiers')
            ->once()
            ->with('preset')
            ->andReturn(['w' => 150]);

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::same(['w' => 150], $facade->getDefaultModifiers());
    }

    public function testLinkShouldBeCreated(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiedPathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('withModifiers')
            ->once()
            ->with(['h' => 100])
            ->andReturn($modifiedPathInfo);

        $linkGenerator->shouldReceive('link')
            ->once()
            ->with($modifiedPathInfo, true)
            ->andReturn('/var/www/h:100/file.png');

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::same('/var/www/h:100/file.png', $facade->createLink(['h' => 100]));
    }

    public function testRelativeLinkShouldBeCreated(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifiedPathInfo = Mockery::mock(PathInfoInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $pathInfo->shouldReceive('withModifiers')
            ->once()
            ->with(['h' => 100])
            ->andReturn($modifiedPathInfo);

        $linkGenerator->shouldReceive('link')
            ->once()
            ->with($modifiedPathInfo, false)
            ->andReturn('var/www/h:100/file.png');

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, false);

        Assert::same('var/www/h:100/file.png', $facade->createLink(['h' => 100]));
    }

    public function testErrorShouldBeTriggeredIfModifierNotFound(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $modifierFacade->shouldReceive('getModifierCollection')
            ->once()
            ->withNoArgs()
            ->andReturn($modifierCollection);

        $modifierCollection->shouldReceive('getByName')
            ->once()
            ->with(Width::class)
            ->andThrows(new InvalidArgumentException('Missing modifier.'));

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::error(
            static fn () => $facade->getModifierAlias(Width::class),
            E_USER_WARNING,
            'Missing modifier.',
        );
    }

    public function testModifierAliasShouldBeReturned(): void
    {
        $linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
        $modifierFacade = Mockery::mock(ModifierFacadeInterface::class);
        $pathInfo = Mockery::mock(PathInfoInterface::class);
        $modifierCollection = Mockery::mock(ModifierCollectionInterface::class);

        $pathInfo->shouldReceive('getModifiers')
            ->once()
            ->withNoArgs()
            ->andReturn(null);

        $modifierFacade->shouldReceive('getModifierCollection')
            ->once()
            ->withNoArgs()
            ->andReturn($modifierCollection);

        $modifierCollection->shouldReceive('getByName')
            ->once()
            ->with(Width::class)
            ->andReturn(new Width());

        $facade = new ArgsFacade($linkGenerator, $modifierFacade, $pathInfo, true);

        Assert::same('w', $facade->getModifierAlias(Width::class));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new ArgsFacadeTest())->run();
