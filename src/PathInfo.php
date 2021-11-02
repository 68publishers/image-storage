<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\FileStorage\PathInfo as BasePathInfo;
use SixtyEightPublishers\FileStorage\Exception\PathInfoException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use SixtyEightPublishers\FileStorage\PathInfoInterface as BasePathInfoInterface;

final class PathInfo extends BasePathInfo implements PathInfoInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface  */
	private $codec;

	/** @var mixed|NULL  */
	private $modifiers;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface $codec
	 * @param string                                                           $namespace
	 * @param string                                                           $name
	 * @param string|NULL                                                      $extension
	 * @param string|array|NULL                                                $modifiers
	 * @param string|NULL                                                      $version
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function __construct(CodecInterface $codec, string $namespace, string $name, ?string $extension, $modifiers = NULL, ?string $version = NULL)
	{
		parent::__construct($namespace, $name, $extension, $version);

		$this->codec = $codec;
		$this->modifiers = $modifiers;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function setExtension(?string $extension): BasePathInfoInterface
	{
		$this->validateExtension($extension);

		return parent::setExtension($extension);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function withExt(string $extension): self
	{
		$this->validateExtension($extension);

		return new static($this->codec, $this->getNamespace(), $this->getName(), $extension, $this->getModifiers(), $this->getVersion());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getModifiers()
	{
		return $this->modifiers;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function withModifiers($modifiers): self
	{
		return new static($this->codec, $this->getNamespace(), $this->getName(), $this->getExtension(), $modifiers, $this->getVersion());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function withEncodedModifiers(string $modifiers): self
	{
		return $this->withModifiers($this->codec->decode(new Value($modifiers)));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPath(): string
	{
		$namespace = $this->getNamespace();
		$modifiers = $this->getModifiers();

		if (NULL === $modifiers) {
			return $namespace === ''
				? $this->getName()
				: sprintf('%s/%s', $namespace, $this->getName());
		}

		$modifier = $this->codec->encode(is_string($modifiers) ? new PresetValue($modifiers) : new Value($modifiers));
		$extension = $this->getExtension() ?? SupportedType::getDefaultExtension();

		return $namespace === ''
			? sprintf('%s/%s.%s', $modifier, $this->getName(), $extension)
			: sprintf('%s/%s/%s.%s', $namespace, $modifier, $this->getName(), $extension);
	}

	/**
	 * @param string|NULL $extension
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	private function validateExtension(?string $extension): void
	{
		if (NULL !== $extension && !SupportedType::isExtensionSupported($extension)) {
			throw PathInfoException::unsupportedExtension($extension);
		}
	}
}
