<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Config;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Config\NoImageConfig;

require __DIR__ . '/../bootstrap.php';

final class NoImageConfigTest extends TestCase
{
	public function testConfigShouldBeCreated(): void
	{
		$config = new NoImageConfig(
			'noimage/noimage.png',
			[
				'test' => 'test/noimage.png',
			],
			[
				'test' => '^test\/',
			]
		);

		Assert::same('noimage/noimage.png', $config->getDefaultPath());
		Assert::same([
			'test' => 'test/noimage.png',
		], $config->getPaths());
		Assert::same([
			'test' => '^test\/',
		], $config->getPatterns());
	}
}

(new NoImageConfigTest())->run();
