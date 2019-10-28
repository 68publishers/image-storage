<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use Nette;
use SixtyEightPublishers;

final class SrcSetGenerator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var array  */
	private $results = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator    $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		SixtyEightPublishers\ImageStorage\Config\Env $env
	) {
		$this->linkGenerator = $linkGenerator;
		$this->modifierFacade = $modifierFacade;
		$this->env = $env;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                         $info
	 * @param array|NULL                                                           $modifier
	 *
	 * @return string
	 * @throws \Nette\Utils\JsonException
	 */
	public function generate(Descriptor\IDescriptor $descriptor, SixtyEightPublishers\ImageStorage\ImageInfo $info, ?array $modifier = NULL): string
	{
		[ $modifier, $key ] = $this->parseModifierAndKey($descriptor, $info, $modifier);

		if (array_key_exists($key, $this->results)) {
			return $this->results[$key];
		}

		return $this->results[$key] = $descriptor->createSrcSet(new Descriptor\ArgsFacade(
			$this->linkGenerator,
			$this->modifierFacade,
			$info,
			$modifier
		));
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo                         $info
	 * @param array|NULL                                                           $modifier
	 *
	 * @return array
	 * @throws \Nette\Utils\JsonException
	 */
	protected function parseModifierAndKey(Descriptor\IDescriptor $descriptor, SixtyEightPublishers\ImageStorage\ImageInfo $info, ?array $modifier = NULL): array
	{
		if (NULL !== $modifier) {
			ksort($modifier);
			$key = Nette\Utils\Json::encode($modifier);
		}

		return [
			$modifier,
			$descriptor . '::' . $info->createPath($key ?? $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER]),
		];
	}
}
