<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Security;

use Mockery;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Security\KnownModifiers;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategy;
use Tester\Assert;
use Tester\TestCase;
use function hash_equals;
use function hash_hmac;

require __DIR__ . '/../bootstrap.php';

final class SignatureStrategyTest extends TestCase
{
    public function testTokenShouldBeCreatedAndVerifiedWithEmptyConfig(): void
    {
        $strategy = new SignatureStrategy($this->createConfig(null, null, false), new KnownModifiers([]));
        $token = $strategy->createToken('var/www/file.png');

        Assert::true(hash_equals(
            $token,
            hash_hmac('sha256', 'var/www/file.png', ''),
        ));

        Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
    }

    public function testTokenShouldBeCreatedAndVerifiedWithKeyOption(): void
    {
        $strategy = new SignatureStrategy($this->createConfig(null, 'my_secret', false), new KnownModifiers([]));
        $token = $strategy->createToken('var/www/file.png');

        Assert::true(hash_equals(
            $token,
            hash_hmac('sha256', 'var/www/file.png', 'my_secret'),
        ));

        Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
    }

    public function testTokenShouldBeCreatedAndVerifiedWithAlgorithmOption(): void
    {
        $strategy = new SignatureStrategy($this->createConfig('md5', null, false), new KnownModifiers([]));
        $token = $strategy->createToken('var/www/file.png');

        Assert::true(hash_equals(
            $token,
            hash_hmac('md5', 'var/www/file.png', ''),
        ));

        Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
    }

    public function testTokenShouldBeCreatedAndVerifiedWithPathThatStartsWithSlash(): void
    {
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', false), new KnownModifiers([]));
        $token = $strategy->createToken('/var/www/file.png');

        Assert::true(hash_equals(
            $token,
            hash_hmac('sha256', 'var/www/file.png', 'my_secret'),
        ));

        Assert::true($strategy->verifyToken($token, '/var/www/file.png'));
    }

    public function testInvalidTokenShouldNotBeVerified(): void
    {
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', false), new KnownModifiers([]));

        Assert::false($strategy->verifyToken('invalid_token', 'var/www/file.png'));
    }

    public function testTokenShouldNotBeCreatedForKnownModifiers(): void
    {
        $knownModifiers = new KnownModifiers(['w:100,h:200' => true]);
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', true), $knownModifiers);

        $token = $strategy->createToken('var/www/w:100,h:200/file.png');

        Assert::null($token);
    }

    public function testTokenShouldBeCreatedForUnknownModifiersEvenWhenDisabledOnKnown(): void
    {
        $knownModifiers = new KnownModifiers(['w:100,h:200' => true]);
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', true), $knownModifiers);

        $token = $strategy->createToken('var/www/w:150,h:200/file.png');

        Assert::notNull($token);
        Assert::true(hash_equals(
            $token,
            hash_hmac('sha256', 'var/www/w:150,h:200/file.png', 'my_secret'),
        ));
    }

    public function testKnownModifiersShouldBeVerifiedWithoutToken(): void
    {
        $knownModifiers = new KnownModifiers(['w:100,h:200' => true]);
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', true), $knownModifiers);

        Assert::true($strategy->verifyToken('', 'var/www/w:100,h:200/file.png'));
        Assert::true($strategy->verifyToken('invalid_token', 'var/www/w:100,h:200/file.png'));
    }

    public function testTokenShouldBeCreatedForKnownModifiersWhenNotDisabled(): void
    {
        $knownModifiers = new KnownModifiers(['w:100,h:200' => true]);
        $strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret', false), $knownModifiers);

        $token = $strategy->createToken('var/www/w:100,h:200/file.png');

        Assert::notNull($token);
        Assert::true($strategy->verifyToken($token, 'var/www/w:100,h:200/file.png'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function createConfig(?string $algo, ?string $key, bool $disableOnKnown): ConfigInterface
    {
        $config = Mockery::mock(ConfigInterface::class);

        $config->shouldReceive('offsetGet')
            ->with(Config::SIGNATURE_ALGORITHM)
            ->andReturn($algo);

        $config->shouldReceive('offsetGet')
            ->with(Config::SIGNATURE_KEY)
            ->andReturn($key);

        $config->shouldReceive('offsetGet')
            ->with(Config::DISABLE_SIGNATURE_ON_KNOWN_MODIFIERS)
            ->andReturn($disableOnKnown);

        return $config;
    }
}

(new SignatureStrategyTest())->run();
