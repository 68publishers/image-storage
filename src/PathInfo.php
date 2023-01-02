<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage;

use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\FileStorage\PathInfo as BasePathInfo;
use SixtyEightPublishers\FileStorage\Exception\PathInfoException;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\Value;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\Value\PresetValue;
use function sprintf;
use function is_string;

final class PathInfo extends BasePathInfo implements PathInfoInterface
{
	/**
	 * @param string|array<string, string|numeric|bool>|null $modifiers
	 *
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function __construct(
		private readonly CodecInterface $codec,
		string $namespace,
		string $name,
		?string $extension,
		private string|array|null $modifiers = null,
		?string $version = null,
	) {
		$this->validateExtension($extension);

		parent::__construct($namespace, $name, $extension, $version);
	}

	/**
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function withExtension(?string $extension): static
	{
		$this->validateExtension($extension);

		return parent::withExtension($extension);
	}

	/**
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	public function withExt(?string $extension): static
	{
		$this->validateExtension($extension);

		return parent::withExt($extension);
	}

	public function getModifiers(): string|array|null
	{
		return $this->modifiers;
	}

	public function withModifiers(string|array|null $modifiers): static
	{
		$info = clone $this;
		$info->modifiers = $modifiers;

		return $info;
	}

	public function withEncodedModifiers(string $modifiers): static
	{
		return $this->withModifiers($this->codec->decode(new Value($modifiers)));
	}

	public function getPath(): string
	{
		$namespace = $this->getNamespace();
		$modifiers = $this->getModifiers();

		if (null === $modifiers) {
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
	 * @throws \SixtyEightPublishers\FileStorage\Exception\PathInfoException
	 */
	private function validateExtension(?string $extension): void
	{
		if (null !== $extension && !SupportedType::isExtensionSupported($extension)) {
			throw PathInfoException::unsupportedExtension($extension);
		}
	}
}
