<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\DI;

use Latte\Loaders\StringLoader;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Container;
use SixtyEightPublishers\FileStorage\Exception\RuntimeException;
use Tester\Assert;
use Tester\TestCase;
use function assert;

require __DIR__ . '/../../../bootstrap.php';

final class ImageStorageLatteExtensionTest extends TestCase
{
    public function testExceptionShouldBeThrownIfImageStorageExtensionNotRegistered(): void
    {
        Assert::exception(
            static fn () => ContainerFactory::create(__DIR__ . '/config/ImageStorageLatte/config.error.missingImageStorageExtension.neon'),
            RuntimeException::class,
            "The extension SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageLatteExtension can be used only with SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\ImageStorageExtension.",
        );
    }

    /**
     * @dataProvider getTestingCodes
     */
    public function testLatteExtensionFunctions(string $latteCode, string $htmlCode): void
    {
        $container = ContainerFactory::create(__DIR__ . '/config/ImageStorageLatte/config.neon');

        $this->assertLatte($container, [
            $latteCode => $htmlCode,
        ]);
    }

    public function getTestingCodes(): array
    {
        return [
            ['<img src="{no_image()->withModifiers([\'w\' => 100])}" alt="">', '<img src="/images/noimage/w:100/noimage.png" alt="">'],
            ['<img src="{no_image(null, \'images\')->withModifiers([\'w\' => 100])}" alt="">', '<img src="/images/noimage/w:100/noimage.png" alt="">'],
            ['<img src="{no_image(\'test\', \'images\')->withModifiers([\'w\' => 100])}" alt="">', '<img src="/images/test/w:100/noimage.png" alt="">'],
            ['<img src="{no_image(null, \'images2\')->withModifiers([\'w\' => 100])}" alt="">', '<img src="/images2/noimage/w:100/noimage.png" alt="">'],
            ['<img src="{no_image(\'test\', \'images2\')->withModifiers([\'w\' => 100])}" alt="">', '<img src="/images2/test/w:100/noimage.png" alt="">'],

            ['<img srcset="{no_image()->withModifiers([\'h\' => 200])->srcSet(x_descriptor())}" alt="">', '<img srcset="/images/noimage/h:200,pd:1/noimage.png, /images/noimage/h:200,pd:2/noimage.png 2.0x, /images/noimage/h:200,pd:3/noimage.png 3.0x" alt="">'],
            ['<img srcset="{no_image()->withModifiers([\'h\' => 200])->srcSet(x_descriptor(1, 2.5))}" alt="">', '<img srcset="/images/noimage/h:200,pd:1/noimage.png, /images/noimage/h:200,pd:2.5/noimage.png 2.5x" alt="">'],
            ['<img srcset="{no_image()->withModifiers([\'h\' => 200])->srcSet(w_descriptor(100, 200, 300))}" alt="">', '<img srcset="/images/noimage/h:200,w:100/noimage.png 100w, /images/noimage/h:200,w:200/noimage.png 200w, /images/noimage/h:200,w:300/noimage.png 300w" alt="">'],
            ['<img srcset="{no_image()->withModifiers([\'h\' => 200])->srcSet(w_descriptor_range(100, 300, 100))}" alt="">', '<img srcset="/images/noimage/h:200,w:100/noimage.png 100w, /images/noimage/h:200,w:200/noimage.png 200w, /images/noimage/h:200,w:300/noimage.png 300w" alt="">'],
        ];
    }

    private function assertLatte(Container $container, array $assertions, array $params = []): void
    {
        $latteFactory = $container->getByType(LatteFactory::class);
        assert($latteFactory instanceof LatteFactory);
        $engine = $latteFactory->create();

        $engine->setLoader(new StringLoader());

        foreach ($assertions as $latteCode => $expected) {
            $rendered = $engine->renderToString($latteCode, $params);

            Assert::contains($expected, $rendered);
        }
    }
}

(new ImageStorageLatteExtensionTest())->run();
