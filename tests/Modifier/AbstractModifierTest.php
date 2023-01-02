<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\AbstractModifier;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;

require __DIR__ . '/../bootstrap.php';

final class AbstractModifierTest extends TestCase
{
	public function testModifierWithAliasPropertyShouldBeCreated(): void
	{
		$class = new class() extends AbstractModifier {
			protected ?string $alias = 'a';
		};

		Assert::same('a', $class->getAlias());
	}

	public function testModifierWithAliasArgumentShouldBeCreated(): void
	{
		$class = new class('b') extends AbstractModifier {
			protected ?string $alias = 'a';
		};

		Assert::same('b', $class->getAlias());
	}

	public function testExceptionShouldBeThrownIfAliasIsNotDefined(): void
	{
		Assert::exception(
			static fn () =>new class() extends AbstractModifier {
			},
			InvalidStateException::class,
			'Default value for %A%::$alias is not set!'
		);
	}
}

(new AbstractModifierTest())->run();
