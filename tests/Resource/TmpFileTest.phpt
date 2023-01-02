<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Resource;

use Tester\Assert;
use Tester\TestCase;
use Nette\Utils\FileSystem;
use SixtyEightPublishers\ImageStorage\Resource\TmpFile;
use function md5;
use function file_exists;
use function sys_get_temp_dir;

require __DIR__ . '/../bootstrap.php';

final class TmpFileTest extends TestCase
{
	public function testFileShouldBeUnlinkedViaMethod(): void
	{
		$filename = $this->createFile(__METHOD__);
		Assert::true(file_exists($filename));

		try {
			$tmpFile = new TmpFile($filename);

			$tmpFile->unlink();
			Assert::false(file_exists($filename));
		} finally {
			@unlink($filename);
		}
	}

	public function testFileShouldBeUnlinkedViaDestructor(): void
	{
		$filename = $this->createFile(__METHOD__);
		Assert::true(file_exists($filename));

		try {
			$tmpFile = new TmpFile($filename);

			unset($tmpFile);
			Assert::false(file_exists($filename));
		} finally {
			@unlink($filename);
		}
	}

	private function createFile(string $name): string
	{
		$filename = sys_get_temp_dir() . '/' . md5($name);
		FileSystem::write($filename, 'test');

		return $filename;
	}
}

(new TmpFileTest())->run();
