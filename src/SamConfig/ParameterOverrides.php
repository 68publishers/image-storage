<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\SamConfig;

use Nette;
use SixtyEightPublishers;

final class ParameterOverrides implements \ArrayAccess, \Countable
{
	use Nette\SmartObject;

	/** @var array  */
	private $parameters = [];

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		if (0 >= $this->count()) {
			return '';
		}

		$parameters = [];

		foreach ($this->parameters as $offset => $parameter) {
			if (is_array($parameter)) { # CommaDelimitedList
				$parameter = implode(',', $parameter);
			}

			$parameters[] = $offset . '="' . $parameter . '"';
		}

		return implode(' ', $parameters);
	}

	/**
	 * @param $offset
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	private function checkOffsetExists($offset): void
	{
		if (!$this->offsetExists($offset)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidStateException(sprintf(
				'Missing parameter with name %s',
				$offset
			));
		}
	}

	/************** interface \ArrayAccess **************/

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->parameters);
	}

	/**
	 * {@inheritDoc}
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function offsetGet($offset)
	{
		$this->checkOffsetExists($offset);

		return $this->parameters[$offset];
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetSet($offset, $value): void
	{
		$this->parameters[$offset] = $value;
	}

	/**
	 * {@inheritDoc}
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidStateException
	 */
	public function offsetUnset($offset): void
	{
		$this->checkOffsetExists($offset);
		unset($this->parameters[$offset]);
	}

	/************** interface \Countable **************/

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return count($this->parameters);
	}
}
