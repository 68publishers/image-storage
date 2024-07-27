<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Responsive;

use SixtyEightPublishers\ImageStorage\Responsive\SrcSet;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class SrcSetTest extends TestCase
{
    public function testSrcSetShouldBeConvertedToString(): void
    {
        $srcSet = new SrcSet(
            descriptor: 'w',
            links: [
                100 => 'var/www/h:100,w:100/file.png',
                200 => 'var/www/h:100,w:200/file.png',
                300 => 'var/www/h:100,w:300/file.png',
            ],
            value: 'var/www/h:100,w:100/file.png 100w, var/www/h:100,w:200/file.png 200w, var/www/h:100,w:300/file.png 300w',
        );

        Assert::same('var/www/h:100,w:100/file.png 100w, var/www/h:100,w:200/file.png 200w, var/www/h:100,w:300/file.png 300w', $srcSet->toString());
        Assert::same('var/www/h:100,w:100/file.png 100w, var/www/h:100,w:200/file.png 200w, var/www/h:100,w:300/file.png 300w', (string) $srcSet);
    }
}

(new SrcSetTest())->run();
