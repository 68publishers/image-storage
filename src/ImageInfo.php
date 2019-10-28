<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use Nette;
use League;
use SixtyEightPublishers;

class ImageInfo
{
	use Nette\SmartObject;

	private const TRIM_CHARACTERS = Nette\Utils\Strings::TRIM_CHARACTERS . "\\/";

	/** @var string  */
	private $namespace;

	/** @var string  */
	private $name;

	/** @var NULL|string */
	private $version;

	/** @var bool  */
	private $isNoImage;

	/**
	 * @param string $path
	 * @param bool   $isNoImage
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function __construct(string $path, bool $isNoImage = FALSE)
	{
		$parts = explode('/', $path);
		$this->setName(Nette\Utils\Strings::trim(array_pop($parts), self::TRIM_CHARACTERS));
		$this->setNamespace(Nette\Utils\Strings::trim(implode('/', $parts), self::TRIM_CHARACTERS));

		$this->isNoImage = $isNoImage;
	}

	/**
	 * @param string $namespace
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function setNamespace(string $namespace): ImageInfo
	{
		$this->namespace = $namespace;

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function setName(string $name): ImageInfo
	{
		if ($name === '') {
			throw SixtyEightPublishers\ImageStorage\Exception\ImageInfoException::invalidPath($name);
		}

		$this->name = $name;

		return $this;
	}

	/**
	 * @param string|NULL $version
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 */
	public function setVersion(?string $version): ImageInfo
	{
		$this->version = $version;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return NULL|string
	 */
	public function getVersion(): ?string
	{
		return $this->version;
	}

	/**
	 * @return bool
	 */
	public function isNoImage(): bool
	{
		return $this->isNoImage;
	}

	/**
	 * @return string
	 */
	public function getContentType(): string
	{
		/** @noinspection PhpInternalEntityUsedInspection */
		return League\Flysystem\Util\MimeType::detectByFilename($this->getName());
	}

	/**
	 * @param string $modifier
	 *
	 * @return string
	 */
	public function createPath(string $modifier): string
	{
		return $this->namespace === ''
			? sprintf('%s/%s', $modifier, $this->name)
			: sprintf('%s/%s/%s', $this->namespace, $modifier, $this->name);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->namespace === ''
			? $this->name
			: sprintf('%s/%s', $this->namespace, $this->name);
	}
}
