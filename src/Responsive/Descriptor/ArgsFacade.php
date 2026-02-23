<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use function is_array;
use function trigger_error;

final class ArgsFacade
{
    /** @var array<string, string|numeric|bool>|null */
    private ?array $defaultModifiers = null;

    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
        private readonly ModifierFacadeInterface $modifierFacade,
        private readonly PathInfoInterface $pathInfo,
        private readonly bool $absolute,
    ) {
        $modifiers = $this->pathInfo->getModifiers();

        if (null !== $modifiers) {
            $this->defaultModifiers = is_array($modifiers)
                ? $modifiers
                : $this->modifierFacade->getCodec()->expandModifiers(value: $modifiers);
        }
    }

    /**
     * @return array<string, string|numeric|bool>|null
     */
    public function getDefaultModifiers(): ?array
    {
        return $this->defaultModifiers;
    }

    /**
     * @param array<string, string|numeric|bool> $modifiers
     */
    public function createLink(array $modifiers): string
    {
        return $this->linkGenerator->link(
            pathInfo: $this->pathInfo->withModifiers($modifiers),
            absolute: $this->absolute,
        );
    }

    public function getModifierAlias(string $modifierClassName): ?string
    {
        try {
            return $this->modifierFacade
                ->getModifierCollection()
                ->getByName($modifierClassName)
                ->getAlias();
        } catch (InvalidArgumentException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return null;
    }
}
