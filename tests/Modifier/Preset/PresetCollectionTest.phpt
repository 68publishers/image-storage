<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Modifier\Preset;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\Preset;
use SixtyEightPublishers\ImageStorage\Modifier\Preset\PresetCollection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class PresetCollectionTest extends TestCase
{
    public function testPresetsShouldBeAdded(): void
    {
        $collection = new PresetCollection();

        $collection->add('a', new Preset(['w' => 15], null, null));
        $collection->add('b', new Preset(['w' => 15, 'pd' => 2.0], null, null));

        Assert::true($collection->has('a'));
        Assert::true($collection->has('b'));
        Assert::false($collection->has('c'));

        Assert::same(['w' => 15], $collection->get('a')->modifiers);
        Assert::same(['w' => 15, 'pd' => 2.0], $collection->get('b')->modifiers);

        Assert::exception(
            static fn () => $collection->get('c'),
            InvalidArgumentException::class,
            'Preset with the alias "c" is not defined in the collection, please check your configuration.',
        );
    }
}

(new PresetCollectionTest())->run();
