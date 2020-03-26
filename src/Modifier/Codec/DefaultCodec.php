<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use Nette;
use SixtyEightPublishers;

final class DefaultCodec implements ICodec
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Config  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection  */
	private $collection;

	/** @var array  */
	private $decodeCache = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Config                           $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Config $config,
		SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection
	) {
		$this->config = $config;
		$this->collection = $collection;
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec ****************/

	/**
	 * {@inheritdoc}
	 */
	public function encode(array $parameters): string
	{
		$result = [];
		ksort($parameters);
		$assigner = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_ASSIGNER];

		foreach ($parameters as $k => $v) {
			$modifier = $this->collection->getByAlias($k);

			if (!$modifier instanceof SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier) {
				if (TRUE === (bool) $v) {
					$result[] = $k;
				}

				continue;
			}

			$result[] = $k . $assigner . ((string) $v);
		}

		if (empty($parameters)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException('Parameters can\`t be empty.');
		}

		return implode($this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_SEPARATOR], $result);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decode(string $path): array
	{
		if (isset($this->decodeCache[$path])) {
			return $this->decodeCache[$path];
		}

		if (empty($path)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException('Path can\`t be empty.');
		}

		$parameters = [];
		$assigner = $this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_ASSIGNER];

		foreach (explode($this->config[SixtyEightPublishers\ImageStorage\Config\Config::MODIFIER_SEPARATOR], $path) as $modifier) {
			$modifier = explode($assigner, $modifier);
			$count = count($modifier);

			if (1 > $count || 2 < $count) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" has invalid format.',
					$path,
					implode($assigner, $modifier)
				));
			}

			$modifierObject = $this->collection->getByAlias($modifier[0]);

			if (1 === $count && $modifierObject instanceof SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" must have a value.',
					$path,
					$modifierObject->getAlias()
				));
			}

			if (2 === $count && !$modifierObject instanceof SixtyEightPublishers\ImageStorage\Modifier\IParsableModifier) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" can\'t have a value.',
					$path,
					$modifierObject->getAlias()
				));
			}

			$parameters[$modifierObject->getAlias()] = 2 === $count ? $modifier[1] : TRUE;
		}

		return $this->decodeCache[$path] = $parameters;
	}
}
