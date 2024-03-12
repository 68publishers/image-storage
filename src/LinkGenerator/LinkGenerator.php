<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\LinkGenerator;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGenerator as FileLinkGenerator;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use function assert;
use function is_string;
use function sprintf;

final class LinkGenerator extends FileLinkGenerator implements LinkGeneratorInterface
{
    private ?SrcSetGenerator $srcSetGenerator = null;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly ModifierFacadeInterface $modifierFacade,
        private readonly SrcSetGeneratorFactoryInterface $srcSetGeneratorFactory,
        private readonly ?SignatureStrategyInterface $signatureStrategy = null,
    ) {
        parent::__construct($this->config);
    }

    public function link(FilePathInfoInterface $pathInfo): string
    {
        if (!$pathInfo instanceof ImagePathInfoInterface) {
            throw new InvalidArgumentException(sprintf(
                'Path info passed into the method %s() must be an instance of %s.',
                __METHOD__,
                ImagePathInfoInterface::class,
            ));
        }

        if (null === $pathInfo->getModifiers()) {
            throw new InvalidArgumentException('Links to source images can not be created.');
        }

        return parent::link($pathInfo);
    }

    public function srcSet(ImagePathInfoInterface $info, DescriptorInterface $descriptor): string
    {
        if (null === $this->srcSetGenerator) {
            $this->srcSetGenerator = $this->srcSetGeneratorFactory->create($this, $this->modifierFacade);
        }

        return $this->srcSetGenerator->generate($descriptor, $info);
    }

    public function getSignatureStrategy(): ?SignatureStrategyInterface
    {
        return $this->signatureStrategy;
    }

    protected function buildQueryParams(FilePathInfoInterface $pathInfo): array
    {
        $params = parent::buildQueryParams($pathInfo);

        if (null !== $this->signatureStrategy) {
            $signatureParameterName = $this->config[Config::SIGNATURE_PARAMETER_NAME];
            assert(is_string($signatureParameterName));

            $params[$signatureParameterName] = $this->signatureStrategy->createToken($pathInfo->getPath());
        }

        return $params;
    }
}
