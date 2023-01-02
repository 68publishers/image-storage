<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Collection;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\ParsableModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection;

require __DIR__ . '/../../bootstrap.php';

final class ModifierCollectionTest extends TestCase
{
	public function testModifiersShouldBeAddedAndReturned(): void
	{
		$modifierCollection = new ModifierCollection();
		$modifier1 = Mockery::mock(ModifierInterface::class);
		$modifier2 = Mockery::mock(ModifierInterface::class);

		$modifier1->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier1->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifier2->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier2');

		$modifier2->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m2');

		$modifierCollection->add($modifier1);
		$modifierCollection->add($modifier2);

		Assert::true($modifierCollection->hasByName('Modifier1'));
		Assert::true($modifierCollection->hasByName('Modifier2'));
		Assert::false($modifierCollection->hasByName('Modifier3'));

		Assert::true($modifierCollection->hasByAlias('m1'));
		Assert::true($modifierCollection->hasByAlias('m2'));
		Assert::false($modifierCollection->hasByAlias('m3'));

		Assert::same($modifier1, $modifierCollection->getByName('Modifier1'));
		Assert::same($modifier2, $modifierCollection->getByName('Modifier2'));
		Assert::exception(
			static fn () => $modifierCollection->getByName('Modifier3'),
			InvalidArgumentException::class,
			'Modifier with the name "Modifier3" is not defined in the collection.'
		);

		Assert::same($modifier1, $modifierCollection->getByAlias('m1'));
		Assert::same($modifier2, $modifierCollection->getByAlias('m2'));
		Assert::exception(
			static fn () => $modifierCollection->getByAlias('m3'),
			InvalidArgumentException::class,
			'Modifier with the alias "m3" is not defined in the collection.'
		);
	}

	public function testExceptionShouldBeThrownIfModifierWithDuplicatedNameAdded(): void
	{
		$modifierCollection = new ModifierCollection();
		$modifier1 = Mockery::mock(ModifierInterface::class);
		$modifier2 = Mockery::mock(ModifierInterface::class);

		$modifier1->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier1->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifier2->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier2->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m2');

		$modifierCollection->add($modifier1);

		Assert::exception(
			static fn () => $modifierCollection->add($modifier2),
			InvalidArgumentException::class,
			'Duplicated modifier with the name "Modifier1" and the alias "m2" passed into SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection::add(). Names and the aliases must be unique.'
		);
	}

	public function testExceptionShouldBeThrownIfModifierWithDuplicatedAliasAdded(): void
	{
		$modifierCollection = new ModifierCollection();
		$modifier1 = Mockery::mock(ModifierInterface::class);
		$modifier2 = Mockery::mock(ModifierInterface::class);

		$modifier1->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier1->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifier2->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier2');

		$modifier2->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifierCollection->add($modifier1);

		Assert::exception(
			static fn () => $modifierCollection->add($modifier2),
			InvalidArgumentException::class,
			'Duplicated modifier with the name "Modifier2" and the alias "m1" passed into SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollection::add(). Names and the aliases must be unique.'
		);
	}

	public function testModifierCollectionShouldBeIterable(): void
	{
		$modifierCollection = new ModifierCollection();
		$modifier1 = Mockery::mock(ModifierInterface::class);
		$modifier2 = Mockery::mock(ModifierInterface::class);

		$modifier1->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier1->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifier2->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier2');

		$modifier2->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m2');

		$modifierCollection->add($modifier1);
		$modifierCollection->add($modifier2);

		Assert::same([
			'Modifier1' => $modifier1,
			'Modifier2' => $modifier2,
		], iterator_to_array($modifierCollection));
	}

	public function testValuesShouldBeParsed(): void
	{
		$modifierCollection = new ModifierCollection();
		$modifier1 = Mockery::mock(ModifierInterface::class);
		$modifier2 = Mockery::mock(ModifierInterface::class);
		$parsableModifierWithValue = Mockery::mock(ParsableModifierInterface::class);
		$parsableModifierWithNullValue = Mockery::mock(ParsableModifierInterface::class);

		$modifier1->shouldReceive('getName')
			->times(2)
			->withNoArgs()
			->andReturn('Modifier1');

		$modifier1->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m1');

		$modifier2->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier2');

		$modifier2->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m2');

		$parsableModifierWithValue->shouldReceive('getName')
			->times(2)
			->withNoArgs()
			->andReturn('Modifier3');

		$parsableModifierWithValue->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m3');

		$parsableModifierWithValue->shouldReceive('parseValue')
			->once()
			->with('15')
			->andReturn(15.0);

		$parsableModifierWithNullValue->shouldReceive('getName')
			->once()
			->withNoArgs()
			->andReturn('Modifier4');

		$parsableModifierWithNullValue->shouldReceive('getAlias')
			->once()
			->withNoArgs()
			->andReturn('m4');

		$parsableModifierWithNullValue->shouldReceive('parseValue')
			->once()
			->with('no')
			->andReturn(null);

		$modifierCollection->add($modifier1);
		$modifierCollection->add($modifier2);
		$modifierCollection->add($parsableModifierWithValue);
		$modifierCollection->add($parsableModifierWithNullValue);

		$values = $modifierCollection->parseValues([
			'm1' => '1',
			'm2' => '0',
			'm3' => 15,
			'm4' => 'no',
		]);

		Assert::true($values->has('Modifier1'));
		Assert::false($values->has('Modifier2'));
		Assert::true($values->has('Modifier3'));
		Assert::false($values->has('Modifier4'));

		Assert::same(true, $values->get('Modifier1'));
		Assert::same(15.0, $values->get('Modifier3'));
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}
}

(new ModifierCollectionTest())->run();
