<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive\Descriptor;

use Nette;
use SixtyEightPublishers;

final class ArgsFacade
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\ImageInfo  */
	private $imageInfo;

	/** @var array|NULL  */
	private $modifiers;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator    $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                       $imageInfo
	 * @param array|NULL                                                         $modifiers
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		SixtyEightPublishers\ImageStorage\ImageInfo $imageInfo,
		?array $modifiers
	) {
		$this->linkGenerator = $linkGenerator;
		$this->modifierFacade = $modifierFacade;
		$this->imageInfo = $imageInfo;
		$this->modifiers = $modifiers;
	}

	/**
	 * @param array|NULL $modifiers
	 *
	 * @return string
	 */
	public function createLink(?array $modifiers): string
	{
		return $this->linkGenerator->link($this->imageInfo, $modifiers);
	}

	/**
	 * @return array|NULL
	 */
	public function getDefaultModifiers(): ?array
	{
		return $this->modifiers;
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
		} catch (SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}

		return NULL;
	}
}
