<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface;

final class ArgsFacade
{
	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\PathInfoInterface  */
	private $pathInfo;

	/** @var array|NULL */
	private $defaultModifiers;

	/**
	 * @param \SixtyEightPublishers\FileStorage\LinkGenerator\LinkGeneratorInterface     $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\ModifierFacadeInterface $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\PathInfoInterface                       $pathInfo
	 */
	public function __construct(LinkGeneratorInterface $linkGenerator, ModifierFacadeInterface $modifierFacade, PathInfoInterface $pathInfo)
	{
		$this->linkGenerator = $linkGenerator;
		$this->modifierFacade = $modifierFacade;
		$this->pathInfo = $pathInfo;

		$modifiers = $pathInfo->getModifiers();

		if (NULL !== $modifiers) {
			$this->defaultModifiers = is_array($modifiers) ? $modifiers : $modifierFacade->getCodec()->decode(new PresetValue($modifiers));
		}
	}

	/**
	 * @return array|NULL
	 */
	public function getDefaultModifiers(): ?array
	{
		return $this->defaultModifiers;
	}

	/**
	 * @param array $modifiers
	 *
	 * @return string
	 */
	public function createLink(array $modifiers): string
	{
		return $this->linkGenerator->link($this->pathInfo->withModifiers($modifiers));
	}

	/**
	 * @param string $modifierClassName
	 *
	 * @return string|NULL
	 */
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

		return NULL;
	}
}
