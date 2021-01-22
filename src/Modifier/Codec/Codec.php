<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\ValueInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;

final class Codec implements CodecInterface
{
	/** @var \SixtyEightPublishers\FileStorage\Config\ConfigInterface  */
	private $config;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface  */
	private $collection;

	/**
	 * @param \SixtyEightPublishers\FileStorage\Config\ConfigInterface                           $config
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface $collection
	 */
	public function __construct(ConfigInterface $config, ModifierCollectionInterface $collection)
	{
		$this->config = $config;
		$this->collection = $collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function encode(ValueInterface $value): string
	{
		$parameters = (array) $value->getValue();
		$assigner = $this->config[Config::MODIFIER_ASSIGNER];
		$result = [];

		ksort($parameters);


		foreach ($parameters as $k => $v) {
			$modifier = $this->collection->getByAlias($k);

			if (!$modifier instanceof ParsableModifierInterface) {
				if (TRUE === (bool) $v) {
					$result[] = $k;
				}

				continue;
			}

			$result[] = $k . $assigner . ((string) $v);
		}

		if (empty($parameters)) {
			throw new InvalidArgumentException('Parameters can\`t be empty.');
		}

		return implode($this->config[Config::MODIFIER_SEPARATOR], $result);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decode(ValueInterface $value): array
	{
		$path = (string) $value->getValue();

		if (empty($path)) {
			throw new InvalidArgumentException('PathInfo can\`t be empty.');
		}

		$parameters = [];
		$assigner = $this->config[Config::MODIFIER_ASSIGNER];

		foreach (explode($this->config[Config::MODIFIER_SEPARATOR], $path) as $modifier) {
			$modifier = explode($assigner, $modifier);
			$count = count($modifier);

			if (1 > $count || 2 < $count) {
				throw new InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" has invalid format.',
					$path,
					implode($assigner, $modifier)
				));
			}

			$modifierObject = $this->collection->getByAlias($modifier[0]);

			if (1 === $count && $modifierObject instanceof ParsableModifierInterface) {
				throw new InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" must have a value.',
					$path,
					$modifierObject->getAlias()
				));
			}

			if (2 === $count && !$modifierObject instanceof ParsableModifierInterface) {
				throw new InvalidArgumentException(sprintf(
					'An invalid path "%s" passed, a modifier "%s" can\'t have a value.',
					$path,
					$modifierObject->getAlias()
				));
			}

			$parameters[$modifierObject->getAlias()] = 2 === $count ? $modifier[1] : TRUE;
		}

		return $parameters;
	}
}
