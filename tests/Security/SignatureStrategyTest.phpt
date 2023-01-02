<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Security;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\Security\SignatureStrategy;
use function hash_hmac;
use function hash_equals;

require __DIR__ . '/../bootstrap.php';

final class SignatureStrategyTest extends TestCase
{
	public function testTokenShouldBeCreatedAndVerifiedWithEmptyConfig(): void
	{
		$strategy = new SignatureStrategy($this->createConfig(null, null));
		$token = $strategy->createToken('var/www/file.png');

		Assert::true(hash_equals(
			$token,
			hash_hmac('sha256', 'var/www/file.png', '')
		));

		Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
	}

	public function testTokenShouldBeCreatedAndVerifiedWithKeyOption(): void
	{
		$strategy = new SignatureStrategy($this->createConfig(null, 'my_secret'));
		$token = $strategy->createToken('var/www/file.png');

		Assert::true(hash_equals(
			$token,
			hash_hmac('sha256', 'var/www/file.png', 'my_secret')
		));

		Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
	}

	public function testTokenShouldBeCreatedAndVerifiedWithAlgorithmOption(): void
	{
		$strategy = new SignatureStrategy($this->createConfig('md5', null));
		$token = $strategy->createToken('var/www/file.png');

		Assert::true(hash_equals(
			$token,
			hash_hmac('md5', 'var/www/file.png', '')
		));

		Assert::true($strategy->verifyToken($token, 'var/www/file.png'));
	}

	public function testTokenShouldBeCreatedAndVerifiedWithPathThatStartsWithSlash(): void
	{
		$strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret'));
		$token = $strategy->createToken('/var/www/file.png');

		Assert::true(hash_equals(
			$token,
			hash_hmac('sha256', 'var/www/file.png', 'my_secret')
		));

		Assert::true($strategy->verifyToken($token, '/var/www/file.png'));
	}

	public function testInvalidTokenShouldNotBeVerified(): void
	{
		$strategy = new SignatureStrategy($this->createConfig('sha256', 'my_secret'));

		Assert::false($strategy->verifyToken('invalid_token', 'var/www/file.png'));
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function createConfig(?string $algo, ?string $key): ConfigInterface
	{
		$config = Mockery::mock(ConfigInterface::class);

		$config->shouldReceive('offsetGet')
			->with(Config::SIGNATURE_ALGORITHM)
			->andReturn($algo);

		$config->shouldReceive('offsetGet')
			->with(Config::SIGNATURE_KEY)
			->andReturn($key);

		return $config;
	}
}

(new SignatureStrategyTest())->run();
