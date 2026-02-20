<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\FileStorage;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Resource\ResourceFactoryInterface;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfigInterface;
use SixtyEightPublishers\ImageStorage\FileInfoInterface as ImageFileInfoInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerFactoryInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\ImageServerInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\RequestInterface;
use SixtyEightPublishers\ImageStorage\Info\InfoFactoryInterface;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface as ImageLinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\NoImage\NoImageResolverInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use function assert;

final class ImageStorage extends FileStorage implements ImageStorageInterface
{
    private ?ImageServerInterface $imageServer = null;

    public function __construct(
        string $name,
        ConfigInterface $config,
        ResourceFactoryInterface $resourceFactory,
        ImageLinkGeneratorInterface $linkGenerator,
        ImagePersisterInterface $imagePersister,
        private readonly NoImageResolverInterface $noImageResolver,
        private readonly InfoFactoryInterface $infoFactory,
        private readonly ImageServerFactoryInterface $imageServerFactory,
    ) {
        parent::__construct($name, $config, $resourceFactory, $linkGenerator, $imagePersister);
    }

    /**
     * @param string|array<string, string|numeric|bool>|null $modifier
     */
    public function createPathInfo(string $path, string|array|null $modifier = null): ImagePathInfoInterface
    {
        if (empty($path)) {
            return $this->getNoImage()->withModifiers($modifier);
        }

        return $this->infoFactory->createPathInfo($path, $modifier);
    }

    public function createFileInfo(PathInfoInterface $pathInfo): ImageFileInfoInterface
    {
        if (!$pathInfo instanceof ImagePathInfoInterface) {
            $pathInfo = $this->createPathInfo($pathInfo->getPath());
        }

        return $this->infoFactory->createFileInfo($pathInfo);
    }

    public function getImageResponse(RequestInterface $request): object
    {
        if (null === $this->imageServer) {
            $this->imageServer = $this->imageServerFactory->create($this);
        }

        return $this->imageServer->getImageResponse($request);
    }

    public function getNoImageConfig(): NoImageConfigInterface
    {
        return $this->noImageResolver->getNoImageConfig();
    }

    public function getNoImage(?string $name = null): ImagePathInfoInterface
    {
        return $this->noImageResolver->getNoImage($name);
    }

    public function isNoImage(string $path): bool
    {
        return $this->noImageResolver->isNoImage($path);
    }

    public function resolveNoImage(string $path): ImagePathInfoInterface
    {
        return $this->noImageResolver->resolveNoImage($path);
    }

    public function srcSet(ImagePathInfoInterface $info, DescriptorInterface $descriptor, bool $absolute = true): SrcSet
    {
        assert($this->linkGenerator instanceof ImageLinkGeneratorInterface);

        return $this->linkGenerator->srcSet(
            info: $info,
            descriptor: $descriptor,
            absolute: $absolute,
        );
    }

    public function getSignatureStrategy(): ?SignatureStrategyInterface
    {
        assert($this->linkGenerator instanceof ImageLinkGeneratorInterface);

        return $this->linkGenerator->getSignatureStrategy();
    }
}
