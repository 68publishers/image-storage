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
use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGenerator;
use SixtyEightPublishers\ImageStorage\Responsive\SrcSetGeneratorFactoryInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategyInterface;
use function assert;
use function explode;
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

    public function link(FilePathInfoInterface $pathInfo, bool $absolute = true): string
    {
        if (!$pathInfo instanceof ImagePathInfoInterface) {
            throw new InvalidArgumentException(
                message: sprintf(
                    'Path info passed into the method %s() must be an instance of %s.',
                    __METHOD__,
                    ImagePathInfoInterface::class,
                ),
            );
        }

        if (null === $pathInfo->getModifiers() || [] === $pathInfo->getModifiers()) {
            $pathInfo = $pathInfo->withModifiers(['original' => true]);
        }

        return parent::link(
            pathInfo: $pathInfo,
            absolute: $absolute,
        );
    }

    public function srcSet(ImagePathInfoInterface $info, ?DescriptorInterface $descriptor = null, bool $absolute = true): SrcSet
    {
        if (null === $descriptor) {
            $descriptor = $this->resolveDescriptor(
                pathInfo: $info,
            );
        }

        if (null === $this->srcSetGenerator) {
            $this->srcSetGenerator = $this->srcSetGeneratorFactory->create($this, $this->modifierFacade);
        }

        return $this->srcSetGenerator->generate(
            descriptor: $descriptor,
            pathInfo: $info,
            absolute: $absolute,
        );
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

            $token = $this->signatureStrategy->createToken($pathInfo->getPath());

            if (null !== $token) {
                $params[$signatureParameterName] = $token;
            }
        }

        return $params;
    }

    private function resolveDescriptor(ImagePathInfoInterface $pathInfo): DescriptorInterface
    {
        $modifiers = $pathInfo->getModifiers();

        if (is_string($modifiers)) {
            $presets = $this->modifierFacade->getPresetCollection();
            $assigner = $this->config[Config::MODIFIER_ASSIGNER];
            $assigner = empty($assigner) ? ':' : $assigner;
            [$presetAlias] = explode($assigner, $modifiers, 2);
            $preset = $presets->get(presetAlias: $presetAlias);

            if (null !== $preset->descriptor) {
                return $preset->descriptor;
            }
        }

        throw new InvalidArgumentException(
            message: sprintf(
                'Unable to resolve descriptor for path info %s. Descriptor must be provided to the method %s::srcSet() manually.',
                $pathInfo,
                __CLASS__,
            ),
        );
    }
}
