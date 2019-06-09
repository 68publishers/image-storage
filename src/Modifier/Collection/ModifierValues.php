<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use Nette;
use SixtyEightPublishers;

final class ModifierValues
{
	use Nette\SmartObject;

	/** @var array  */
	private $values = [];

	/**
	 * @param array $values
	 */
	public function __construct(array $values = [])
	{
		foreach ($values as $k => $v) {
			$this->add($k, $v);
		}
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	private function add(string $name, $value): void
	{
		$this->values[$name] = $value;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->values[$name]);
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function get(string $name)
	{
		if (!$this->has($name)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Missing value for modifier %s',
				$name
			));
		}

		return $this->values[$name];
	}

	/**
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getOptional(string $name, $default = NULL)
	{
		return $this->has($name) ? $this->values[$name] : $default;
	}
}
