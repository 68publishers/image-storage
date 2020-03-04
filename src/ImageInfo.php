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
		$namespace = explode('/', trim($path, " \t\n\r\0\x0B/"));
		$name = explode('.', array_pop($namespace));
		$extension = 1 < count($name) ? array_pop($name) : NULL;

		$this->setName(implode('.', $name));
		$this->setNamespace(implode('/', $namespace));
		$this->setExtension($extension);

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
	 * @return bool
	 */
	public function isNoImage(): bool
	{
		return $this->isNoImage;
	}

	/**
	 * @param string $modifier
	 *
	 * @return string
	 */
	public function createPath(string $modifier): string
	{
		$namespace = $this->getNamespace();
		$extension = NULL === $this->getExtension() ? '' : '.' . $this->getExtension();

		return $namespace === ''
			? sprintf('%s/%s%s', $modifier, $this->getName(), $extension)
			: sprintf('%s/%s/%s%s', $namespace, $modifier, $this->getName(), $extension);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$namespace = $this->getNamespace();
		$extension = NULL === $this->getExtension() ? '' : '.' . $this->getExtension();

		return $namespace === ''
			? $this->getNamespace() . $extension
			: sprintf('%s/%s%s', $namespace, $this->getName(), $extension);
	}
}
