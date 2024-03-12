<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Info;

use SixtyEightPublishers\FileStorage\Helper\Path;
use SixtyEightPublishers\ImageStorage\FileInfo;
use SixtyEightPublishers\ImageStorage\FileInfoInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfo;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;

final class InfoFactory implements InfoFactoryInterface
{
    public function __construct(
        private readonly ModifierFacadeInterface $modifierFacade,
        private readonly LinkGeneratorInterface $linkGenerator,
        private readonly string $storageName,
    ) {}

    /**
     * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
     */
    public function createPathInfo(string $path, string|array|null $modifier = null): PathInfoInterface
    {
        $args = Path::parse($path);
        $args[] = $modifier;

        return new PathInfo($this->modifierFacade->getCodec(), ...$args);
    }

    public function createFileInfo(PathInfoInterface $pathInfo): FileInfoInterface
    {
        return new FileInfo($this->linkGenerator, $pathInfo, $this->storageName);
    }
}
