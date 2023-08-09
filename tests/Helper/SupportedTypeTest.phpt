<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Helper;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Helper\SupportedType;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

require __DIR__ . '/../bootstrap.php';

final class SupportedTypeTest extends TestCase
{
	public function testSupportedTypes(): void
	{
		Assert::same([
			'image/gif',
			'image/jpeg',
			'image/png',
			'image/webp',
			'image/avif',
		], SupportedType::getSupportedTypes());

		Assert::true(SupportedType::isTypeSupported('image/gif'));
		Assert::true(SupportedType::isTypeSupported('image/jpeg'));
		Assert::true(SupportedType::isTypeSupported('image/png'));
		Assert::true(SupportedType::isTypeSupported('image/webp'));
		Assert::true(SupportedType::isTypeSupported('image/avif'));
		Assert::false(SupportedType::isTypeSupported('image/tiff'));
	}

	public function testSupportedExtensions(): void
	{
		Assert::same([
			'gif',
			'jpg',
			'jpeg',
			'pjpg',
			'png',
			'webp',
			'avif',
		], SupportedType::getSupportedExtensions());

		Assert::true(SupportedType::isExtensionSupported('gif'));
		Assert::true(SupportedType::isExtensionSupported('jpg'));
		Assert::true(SupportedType::isExtensionSupported('jpeg'));
		Assert::true(SupportedType::isExtensionSupported('pjpg'));
		Assert::true(SupportedType::isExtensionSupported('png'));
		Assert::true(SupportedType::isExtensionSupported('webp'));
		Assert::true(SupportedType::isExtensionSupported('avif'));
		Assert::false(SupportedType::isExtensionSupported('tiff'));
	}

	public function testDefaultExtension(): void
	{
		Assert::same('jpg', SupportedType::getDefaultExtension());
	}

	public function testDefaultType(): void
	{
		Assert::same('image/jpeg', SupportedType::getDefaultType());
	}

	public function testExceptionShouldBeThrownOnUnsupportedExtension(): void
	{
		Assert::exception(
			static fn () => SupportedType::getTypeByExtension('tiff'),
			InvalidArgumentException::class,
			'Extension .tiff is not supported.'
		);
	}

	public function testTypeByExtensionShouldBeReturned(): void
	{
		Assert::same('image/gif', SupportedType::getTypeByExtension('gif'));
		Assert::same('image/jpeg', SupportedType::getTypeByExtension('jpg'));
		Assert::same('image/jpeg', SupportedType::getTypeByExtension('jpeg'));
		Assert::same('image/jpeg', SupportedType::getTypeByExtension('pjpg'));
		Assert::same('image/png', SupportedType::getTypeByExtension('png'));
		Assert::same('image/webp', SupportedType::getTypeByExtension('webp'));
		Assert::same('image/avif', SupportedType::getTypeByExtension('avif'));
	}

	public function testExceptionShouldBeThrownOnUnsupportedType(): void
	{
		Assert::exception(
			static fn () => SupportedType::getExtensionByType('image/tiff'),
			InvalidArgumentException::class,
			'Mime type image/tiff is not supported.'
		);
	}

	public function testExtensionByTypeShouldBeReturned(): void
	{
		Assert::same('gif', SupportedType::getExtensionByType('image/gif'));
		Assert::same('jpg', SupportedType::getExtensionByType('image/jpeg'));
		Assert::same('png', SupportedType::getExtensionByType('image/png'));
		Assert::same('webp', SupportedType::getExtensionByType('image/webp'));
		Assert::same('avif', SupportedType::getExtensionByType('image/avif'));
	}

	public function testSupportedTypesShouldBeChanged(): void
	{
		SupportedType::setSupportedTypes([
			'png' => 'image/png',
			'tiff' => 'image/tiff',
		]);

		Assert::same([
			'image/png',
			'image/tiff',
		], SupportedType::getSupportedTypes());

		Assert::same([
			'png',
			'tiff',
		], SupportedType::getSupportedExtensions());
	}

	public function testDefaultExtensionAndTypeShouldBeChanged(): void
	{
		SupportedType::setDefault('png', 'image/png');

		Assert::same('png', SupportedType::getDefaultExtension());
		Assert::same('image/png', SupportedType::getDefaultType());
	}
}

(new SupportedTypeTest())->run();
