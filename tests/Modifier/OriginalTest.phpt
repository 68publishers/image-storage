<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\Original;

require __DIR__ . '/../bootstrap.php';

final class OriginalTest extends TestCase
{
	public function testNameShouldBeReturned(): void
	{
		Assert::same(Original::class, (new Original())->getName());
	}

	public function testAliasShouldBeReturned(): void
	{
		Assert::same('original', (new Original())->getAlias());
	}
}

(new OriginalTest())->run();
