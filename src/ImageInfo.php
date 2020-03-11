<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use Nette;

class ImageInfo
{
	use Nette\SmartObject;

	/** @var string  */
	private $namespace;

	/** @var string  */
	private $name;

	/** @var string|NULL */
	private $extension;

	/** @var string|NULL */
	private $version;

	/**
	 * @param string $path
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function __construct(string $path)
	{
		$namespace = explode('/', trim($path, " \t\n\r\0\x0B/"));
		$name = explode('.', array_pop($namespace));
		$extension = 1 < count($name) ? array_pop($name) : NULL;

		$this->setName(implode('.', $name));
		$this->setNamespace(implode('/', $namespace));
		$this->setExtension($extension);
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
			throw Exception\ImageInfoException::invalidPath($name);
		}

		$this->name = $name;

		return $this;
	}

	/**
	 * @param string|NULL $extension
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function setExtension(?string $extension): ImageInfo
	{
		if (NULL !== $extension && !Helper\SupportedType::isExtensionSupported($extension)) {
			throw Exception\ImageInfoException::unsupportedExtension($extension);
		}

		$this->extension = $extension;

		return $this;
	}

	/**
	 * Alias for ::getExtension() with required value
	 *
	 * @param string $extension
	 *
	 * @return \SixtyEightPublishers\ImageStorage\ImageInfo
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	public function ext(string $extension): ImageInfo
	{
		return $this->setExtension($extension);
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
	public function getExtension(): ?string
	{
		return $this->extension;
	}

	/**
	 * @return NULL|string
	 */
	public function getVersion(): ?string
	{
		return $this->version;
	}

	/**
	 * @param string $modifier
	 *
	 * @return string
	 */
	public function createCachedPath(string $modifier): string
	{
		$namespace = $this->getNamespace();
		$extension = $this->getExtension() ?? Helper\SupportedType::getDefaultExtension();

		return $namespace === ''
			? sprintf('%s/%s%s', $modifier, $this->getName(), $extension)
			: sprintf('%s/%s/%s.%s', $namespace, $modifier, $this->getName(), $extension);
	}

	/**
	 * @return string
	 */
	public function createSourcePath(): string
	{
		$namespace = $this->getNamespace();

		return $namespace === ''
			? $this->getName()
			: sprintf('%s/%s', $namespace, $this->getName());
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->createSourcePath() . (NULL === $this->getExtension() ? '' : '.' . $this->getExtension());
	}
}
