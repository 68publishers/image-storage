<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Tests\Bridge\Nette\ImageServer;

use Mockery;
use Tester\Assert;
use Tester\TestCase;
use Nette\Http\IRequest;
use Nette\Utils\Helpers;
use Nette\Http\IResponse;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToRetrieveMetadata;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ImageResponse;

require __DIR__ . '/../../../bootstrap.php';

final class ImageResponseTest extends TestCase
{
	public function testErrorResponseShouldBeSentIfFileNotExists(): void
	{
		$httpResponse = Mockery::mock(IResponse::class);
		$httpRequest = Mockery::mock(IRequest::class);
		$filesystem = $this->createFilesystem();

		$httpResponse->shouldReceive('setCode')
			->once()
			->with(404)
			->andReturnSelf();

		$response = new ImageResponse($filesystem, 'test/w:100/image.png', 31536000);
		$output = Helpers::capture(static fn () => $response->send($httpRequest, $httpResponse));

		Assert::same('{"code":404,"message":"Unable to read file."}', $output);
	}

	public function testErrorResponseShouldBeSentIfFilesystemExceptionIsThrown(): void
	{
		$httpResponse = Mockery::mock(IResponse::class);
		$httpRequest = Mockery::mock(IRequest::class);
		$filesystem = Mockery::instanceMock($this->createFilesystem([
			'test/w:100/image.png' => '... image content ...',
		]));

		$filesystem->shouldReceive('mimeType')
			->once()
			->with('test/w:100/image.png')
			->andThrows(UnableToRetrieveMetadata::mimeType('test/w:100/image.png', 'test'));

		$httpResponse->shouldReceive('setCode')
			->once()
			->with(500)
			->andReturnSelf();

		$response = new ImageResponse($filesystem, 'test/w:100/image.png', 31536000);
		$output = Helpers::capture(static fn () => $response->send($httpRequest, $httpResponse));

		Assert::match('{"code":500,"message":"Filesystem error. %A%"}', $output);
	}

	public function testImageResponseShouldBeSent(): void
	{
		$httpResponse = Mockery::mock(IResponse::class);
		$httpRequest = Mockery::mock(IRequest::class);
		$filesystem = $this->createFilesystem([
			'test/w:100/image.png' => '... image content ...',
		]);

		$httpResponse->shouldReceive('setHeader')
			->once()
			->with('Content-Type', 'image/png')
			->andReturnSelf();

		$httpResponse->shouldReceive('setHeader')
			->once()
			->with('Content-Length', '21')
			->andReturnSelf();

		$httpResponse->shouldReceive('setHeader')
			->once()
			->with('Cache-Control', 'public, max-age=31536000')
			->andReturnSelf();

		$httpResponse->shouldReceive('setHeader')
			->once()
			->with('Expires', Mockery::type('string'))
			->andReturnSelf();

		$response = new ImageResponse($filesystem, 'test/w:100/image.png', 31536000);
		$output = Helpers::capture(static fn () => $response->send($httpRequest, $httpResponse));

		Assert::match('... image content ...', $output);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function createFilesystem(array $files = []): Filesystem
	{
		$filesystem = new Filesystem(
			new InMemoryFilesystemAdapter(mimeTypeDetector: new ExtensionMimeTypeDetector()),
		);

		foreach ($files as $filename => $content) {
			$filesystem->write($filename, $content);
		}

		return $filesystem;
	}
}

(new ImageResponseTest())->run();
