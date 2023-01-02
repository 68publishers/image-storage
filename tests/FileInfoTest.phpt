<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests;

use Closure;
use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\FileInfo;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\LinkGenerator\LinkGeneratorInterface;
use SixtyEightPublishers\FileStorage\PathInfoInterface as FilePathInfoInterface;
use SixtyEightPublishers\ImageStorage\Responsive\Descriptor\DescriptorInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface as ImagePathInfoInterface;
use function call_user_func;

require __DIR__ . '/bootstrap.php';

final class FileInfoTest extends TestCase
{
	public function testFileInfoShouldBeCreatedWithFilePathInfo(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(FilePathInfoInterface::class);

		Assert::noError(static fn () => new FileInfo($linkGenerator, $pathInfo, 'default'));
	}

	public function testFileInfoShouldBeCreatedWithImagePathInfo(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);

		Assert::noError(static fn () => new FileInfo($linkGenerator, $pathInfo, 'default'));
	}

	public function testSrcSetShouldBeReturned(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$descriptor = Mockery::mock(DescriptorInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$linkGenerator->shouldReceive('srcSet')
			->once()
			->with($fileInfo, $descriptor)
			->andReturn('srcset');

		Assert::same('srcset', $fileInfo->srcSet($descriptor));
	}

	public function testModifiersShouldBeNullIfFilePathInfoPassed(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(FilePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		Assert::null($fileInfo->getModifiers());
	}

	public function testModifiersShouldBeReturned(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('getModifiers')
			->once()
			->withNoArgs()
			->andReturn(['w' => 15, 'h' => 15]);

		Assert::same(['w' => 15, 'h' => 15], $fileInfo->getModifiers());
	}

	public function testExceptionShouldBeThrownWhenModifiersAreChangedIfFilePathInfoIsPassed(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(FilePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		Assert::exception(
			static fn () => $fileInfo->withModifiers([]),
			InvalidStateException::class,
			'An instance of %A% must be implementor of an interface SixtyEightPublishers\ImageStorage\PathInfoInterface if you want to use the method SixtyEightPublishers\ImageStorage\FileInfo::withModifiers().'
		);
	}

	public function testModifiersShouldBeChanged(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$modifiedArrayPathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$modifiedPresetPathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$modifiedNullPathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('withModifiers')
			->once()
			->with(['w' => 15, 'h' => 15])
			->andReturn($modifiedArrayPathInfo);

		$pathInfo->shouldReceive('withModifiers')
			->once()
			->with('preset')
			->andReturn($modifiedPresetPathInfo);

		$pathInfo->shouldReceive('withModifiers')
			->once()
			->with(null)
			->andReturn($modifiedNullPathInfo);

		$modifiedArrayFileInfo = $fileInfo->withModifiers(['w' => 15, 'h' => 15]);
		$modifiedPresetFileInfo = $fileInfo->withModifiers('preset');
		$modifiedNullFileInfo = $fileInfo->withModifiers(null);

		Assert::notSame($fileInfo, $modifiedArrayFileInfo);
		Assert::notSame($fileInfo, $modifiedPresetFileInfo);
		Assert::notSame($fileInfo, $modifiedNullFileInfo);

		$this->assertPathInfo($fileInfo, $pathInfo);
		$this->assertPathInfo($modifiedArrayFileInfo, $modifiedArrayPathInfo);
		$this->assertPathInfo($modifiedPresetFileInfo, $modifiedPresetPathInfo);
		$this->assertPathInfo($modifiedNullFileInfo, $modifiedNullPathInfo);
	}

	public function testExceptionShouldBeThrownWhenEncodedModifiersAreChangedIfFilePathInfoIsPassed(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(FilePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		Assert::exception(
			static fn () => $fileInfo->withEncodedModifiers('w:15,h:15'),
			InvalidStateException::class,
			'An instance of %A% must be implementor of an interface SixtyEightPublishers\ImageStorage\PathInfoInterface if you want to use the method SixtyEightPublishers\ImageStorage\FileInfo::withEncodedModifiers().'
		);
	}

	public function testEncodedModifiersShouldBeChanged(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$modifiedPathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('withEncodedModifiers')
			->once()
			->with('w:15,h:15')
			->andReturn($modifiedPathInfo);

		$modifiedFileInfo = $fileInfo->withEncodedModifiers('w:15,h:15');

		Assert::notSame($fileInfo, $modifiedFileInfo);

		$this->assertPathInfo($fileInfo, $pathInfo);
		$this->assertPathInfo($modifiedFileInfo, $modifiedPathInfo);
	}

	public function testFileInfoShouldBeSerializedIntoJsonWithoutAppendedExtensionIfFilePathInfoPassed(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(FilePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('getPath')
			->once()
			->withNoArgs()
			->andReturn('path');

		$pathInfo->shouldReceive('getVersion')
			->once()
			->withNoArgs()
			->andReturn('123');

		Assert::same('{"path":"path","storage":"default","version":"123"}', json_encode($fileInfo, JSON_THROW_ON_ERROR));
	}

	public function testFileInfoShouldBeSerializedIntoJsonWithoutAppendedExtensionIfModifiersAreNotNull(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('getPath')
			->once()
			->withNoArgs()
			->andReturn('path');

		$pathInfo->shouldReceive('getVersion')
			->once()
			->withNoArgs()
			->andReturn('123');

		$pathInfo->shouldReceive('getModifiers')
			->once()
			->withNoArgs()
			->andReturn(['w' => 15, 'h' => 15]);

		Assert::same('{"path":"path","storage":"default","version":"123"}', json_encode($fileInfo, JSON_THROW_ON_ERROR));
	}

	public function testFileInfoShouldBeSerializedIntoJsonWithoutAppendedExtensionIfExtensionIsNull(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('getPath')
			->once()
			->withNoArgs()
			->andReturn('path');

		$pathInfo->shouldReceive('getVersion')
			->once()
			->withNoArgs()
			->andReturn('123');

		$pathInfo->shouldReceive('getModifiers')
			->once()
			->withNoArgs()
			->andReturn(null);

		$pathInfo->shouldReceive('getExtension')
			->once()
			->withNoArgs()
			->andReturn(null);

		Assert::same('{"path":"path","storage":"default","version":"123"}', json_encode($fileInfo, JSON_THROW_ON_ERROR));
	}

	public function testFileInfoShouldBeSerializedIntoJsonWithAppendedExtensionIfModifiersAreNullAndExtensionIsNotNull(): void
	{
		$linkGenerator = Mockery::mock(LinkGeneratorInterface::class);
		$pathInfo = Mockery::mock(ImagePathInfoInterface::class);
		$fileInfo = new FileInfo($linkGenerator, $pathInfo, 'default');

		$pathInfo->shouldReceive('getPath')
			->once()
			->withNoArgs()
			->andReturn('path');

		$pathInfo->shouldReceive('getVersion')
			->once()
			->withNoArgs()
			->andReturn('123');

		$pathInfo->shouldReceive('getModifiers')
			->once()
			->withNoArgs()
			->andReturn(null);

		$pathInfo->shouldReceive('getExtension')
			->times(2)
			->withNoArgs()
			->andReturn('gif');

		Assert::same('{"path":"path.gif","storage":"default","version":"123"}', json_encode($fileInfo, JSON_THROW_ON_ERROR));
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function assertPathInfo(FileInfo $fileInfo, ImagePathInfoInterface $pathInfo): void
	{
		call_user_func(Closure::bind(
			static function () use ($fileInfo, $pathInfo): void {
				Assert::same($pathInfo, $fileInfo->pathInfo);
			},
			null,
			FileInfo::class
		));
	}
}

(new FileInfoTest())->run();
