<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Responsive;

use Nette;
use SixtyEightPublishers;

class SrcSetGenerator
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator  */
	private $linkGenerator;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\DescriptorIterator  */
	private $descriptorIterator;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var array  */
	private $results = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator    $linkGenerator
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\DescriptorIterator   $descriptorIterator
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\LinkGenerator\ILinkGenerator $linkGenerator,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade,
		DescriptorIterator $descriptorIterator,
		SixtyEightPublishers\ImageStorage\Config\Env $env
	) {
		$this->linkGenerator = $linkGenerator;
		$this->modifierFacade = $modifierFacade;
		$this->descriptorIterator = $descriptorIterator;
		$this->env = $env;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array|NULL                                   $modifier
	 *
	 * @return string
	 * @throws \Nette\Utils\JsonException
	 */
	public function generate(SixtyEightPublishers\ImageStorage\ImageInfo $info, ?array $modifier = NULL): string
	{
		[ $modifier, $key ] = $this->parseModifierAndKey($info, $modifier);

		if (array_key_exists($key, $this->results)) {
			return $this->results[$key];
		}

		$pixelDensityAlias = $this->getPixelDensityAlias();

		$this->results[$key] = $this->descriptorIterator->concat(function (Descriptor $descriptor) use ($info, $modifier, $pixelDensityAlias) {
			if (NULL !== $modifier && NULL !== $pixelDensityAlias) {
				$modifier[$pixelDensityAlias] = $descriptor->pd();
			}

			return sprintf(
				'%s%s',
				$this->linkGenerator->link($info, $modifier),
				(($x = $descriptor->x()) !== '') ? ' ' . $x : ''
			);
		});

		return $this->results[$key];
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array|string|NULL                            $modifier
	 *
	 * @return array
	 * @throws \Nette\Utils\JsonException
	 */
	protected function parseModifierAndKey(SixtyEightPublishers\ImageStorage\ImageInfo $info, ?array $modifier = NULL): array
	{
		if (NULL !== $modifier) {
			ksort($modifier);
			$key = Nette\Utils\Json::encode($modifier);
		}

		return [ $modifier, $info->createPath($key ?? $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER]) ];
	}

	/**
	 * @return string|NULL
	 */
	protected function getPixelDensityAlias(): ?string
	{
		try {
			return $this->modifierFacade
				->getModifierCollection()
				->getByName(SixtyEightPublishers\ImageStorage\Modifier\PixelDensity::class)
				->getAlias();
		} catch (SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}

		return NULL;
	}
}
