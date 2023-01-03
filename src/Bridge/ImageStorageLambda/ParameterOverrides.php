<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda;

use Stringable;
use function count;
use function implode;
use function is_array;
use function array_key_exists;

final class ParameterOverrides implements Stringable
{
	/**
	 * @param array<string, scalar|array<scalar>> $parameters
	 */
	public function __construct(
		public readonly array $parameters,
	) {
	}

	/**
	 * @param array<string, scalar|array<scalar>> $parameters
	 */
	public function withMissingParameters(array $parameters): self
	{
		$newParameters = $this->parameters;

		foreach ($parameters as $offset => $parameter) {
			if (!array_key_exists($offset, $newParameters)) {
				$newParameters[$offset] = $parameter;
			}
		}

		return new self($newParameters);
	}

	public function __toString(): string
	{
		if (0 >= count($this->parameters)) {
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
}
