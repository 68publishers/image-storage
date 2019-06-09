<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Codec;

use Nette;
use SixtyEightPublishers;

final class DefaultCodec implements ICodec
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection  */
	private $collection;

	/** @var array  */
	private $decodeCache = [];

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                              $env
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		SixtyEightPublishers\ImageStorage\Modifier\Collection\IModifierCollection $collection
	) {
		$this->env = $env;
		$this->collection = $collection;
	}

	/**************** interface \SixtyEightPublishers\ImageStorage\Modifier\Codec\ICodec ****************/

	/**
	 * {@inheritdoc}
	 */
	public function encode(array $parameters): string
	{
		if (empty($parameters)) {
			return $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER];
		}

		$result = [];
		ksort($parameters);
		$assigner = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::MODIFIER_ASSIGNER];

		foreach ($parameters as $k => $v) {
			# check existence
			$k = $this->collection->getByAlias($k)->getAlias();

			$result[] = $k . $assigner . ((string) $v);
		}

		return implode($this->env[SixtyEightPublishers\ImageStorage\Config\Env::MODIFIER_SEPARATOR], $result);
	}

	/**
	 * {@inheritdoc}
	 */
	public function decode(string $path): array
	{
		if (isset($this->decodeCache[$path])) {
			return $this->decodeCache[$path];
		}

		if ($path === $this->env[SixtyEightPublishers\ImageStorage\Config\Env::ORIGINAL_MODIFIER]) {
			return [];
		}

		$parameters = [];
		$assigner = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::MODIFIER_ASSIGNER];

		foreach (explode($this->env[SixtyEightPublishers\ImageStorage\Config\Env::MODIFIER_SEPARATOR], $path) as $modifier) {
			$modifier = explode($assigner, $modifier);

			if (2 !== count($modifier)) {
				throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
					'Invalid path "%s" passed, modifier "%s" has invalid format.',
					$path,
					implode($assigner, $modifier)
				));
			}

			[ $alias, $value ] = $modifier;
			$modifier = $this->collection->getByAlias($alias);

			$parameters[$modifier->getAlias()] = $value;
		}

		return $this->decodeCache[$path] = $parameters;
	}
}
