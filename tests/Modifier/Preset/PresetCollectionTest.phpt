<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Preset;

use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollection;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;

require __DIR__ . '/../../bootstrap.php';

final class PresetCollectionTest extends TestCase
{
	public function testPresetsShouldBeAdded(): void
	{
		$collection = new PresetCollection();

		$collection->add('a', ['w' => 15]);
		$collection->add('b', ['w' => 15, 'pd' => 2.0]);

		Assert::true($collection->has('a'));
		Assert::true($collection->has('b'));
		Assert::false($collection->has('c'));

		Assert::same(['w' => 15], $collection->get('a'));
		Assert::same(['w' => 15, 'pd' => 2.0], $collection->get('b'));

		Assert::exception(
			static fn () => $collection->get('c'),
			InvalidArgumentException::class,
			'Preset with the alias "c" is not defined in the collection, please check your configuration.'
		);
	}
}

(new PresetCollectionTest())->run();
