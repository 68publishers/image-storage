<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

use Nette;
use SixtyEightPublishers;

final class Env implements \ArrayAccess, \JsonSerializable
{
	use Nette\SmartObject;

	public const    BASE_PATH = 'BASE_PATH',
					HOST = 'HOST',
					VERSION_PARAMETER_NAME = 'VERSION_PARAMETER_NAME',
					ORIGINAL_MODIFIER = 'ORIGINAL_MODIFIER',
					MODIFIER_SEPARATOR = 'MODIFIER_SEPARATOR',
					MODIFIER_ASSIGNER = 'MODIFIER_ASSIGNER',
					ALLOWED_PIXEL_DENSITY = 'ALLOWED_PIXEL_DENSITY',
					ALLOWED_RESOLUTIONS = 'ALLOWED_RESOLUTIONS',
					ALLOWED_QUALITIES = 'ALLOWED_QUALITIES',
					RESIZE_QUALITY = 'RESIZE_QUALITY';

	/** @var array  */
	private $env = [
		self::BASE_PATH => '',
		self::HOST => NULL,
		self::ORIGINAL_MODIFIER => 'original',
		self::MODIFIER_SEPARATOR => ',',
		self::MODIFIER_ASSIGNER => ':',
		self::VERSION_PARAMETER_NAME => NULL,
		self::ALLOWED_PIXEL_DENSITY => [],
		self::ALLOWED_RESOLUTIONS => [],
		self::ALLOWED_QUALITIES => [],
		self::RESIZE_QUALITY => 100,
	];

	/**
	 * @param array $env
	 */
	public function __construct(array $env)
	{
		$this->env = array_merge($this->env, $env);

		// trim base path
		$this->env[self::BASE_PATH] = rtrim((string) $this->env[self::BASE_PATH], '/');

		if (!empty($this->env[self::HOST])) {
			$this->env[self::HOST] = rtrim((string) $this->env[self::HOST], '/');
		}
	}


	/*************** interface \ArrayAccess ***************/

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->env);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException(sprintf(
				'Missing offset %s',
				(string) $offset
			));
		}

		return $this->env[$offset];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value): void
	{
		throw SixtyEightPublishers\ImageStorage\Exception\IllegalMethodCallException::notAllowed(__METHOD__);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset): void
	{
		throw SixtyEightPublishers\ImageStorage\Exception\IllegalMethodCallException::notAllowed(__METHOD__);
	}


	/*************** interface \JsonSerializable ***************/

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize(): array
	{
		return $this->env;
	}
}
