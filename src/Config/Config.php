<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Config;

use Nette;
use SixtyEightPublishers;

final class Config implements \ArrayAccess, \JsonSerializable
{
	use Nette\SmartObject;

	public const    BASE_PATH = 'base_path',
					HOST = 'host',
					VERSION_PARAMETER_NAME = 'version_parameter_name',
					SIGNATURE_PARAMETER_NAME = 'signature_parameter_name',
					SIGNATURE_KEY = 'signature_key',
					SIGNATURE_ALGORITHM = 'signature_algorithm',
					MODIFIER_SEPARATOR = 'modifier_separator',
					MODIFIER_ASSIGNER = 'modifier_assigner',
					ALLOWED_PIXEL_DENSITY = 'allowed_pixel_density',
					ALLOWED_RESOLUTIONS = 'allowed_resolutions',
					ALLOWED_QUALITIES = 'allowed_qualities',
					ENCODE_QUALITY = 'encode_quality',
					CACHE_MAX_AGE = 'cache_max_age';

	/** @var array  */
	private $config = [
		self::BASE_PATH => '',
		self::HOST => NULL,
		self::MODIFIER_SEPARATOR => ',',
		self::MODIFIER_ASSIGNER => ':',
		self::VERSION_PARAMETER_NAME => '_v',
		self::SIGNATURE_PARAMETER_NAME => '_s',
		self::SIGNATURE_KEY => NULL,
		self::SIGNATURE_ALGORITHM => 'sha256',
		self::ALLOWED_PIXEL_DENSITY => [],
		self::ALLOWED_RESOLUTIONS => [],
		self::ALLOWED_QUALITIES => [],
		self::ENCODE_QUALITY => 90,
		self::CACHE_MAX_AGE => 31536000,
	];

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = array_merge($this->config, $config);

		// trim base path
		$this->config[self::BASE_PATH] = trim((string) $this->config[self::BASE_PATH], '/');

		if (!empty($this->config[self::HOST])) {
			$this->config[self::HOST] = rtrim((string) $this->config[self::HOST], '/');
		}
	}

	/*************** interface \ArrayAccess ***************/

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->config);
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

		return $this->config[$offset];
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
		return $this->config;
	}
}
