<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer\Response;

use Nette;
use League;

final class ImageResponse implements Nette\Application\IResponse
{
	use Nette\SmartObject;

	/** @var \League\Flysystem\FilesystemInterface  */
	private $filesystem;

	/** @var string  */
	private $filePath;

	/**
	 * @param \League\Flysystem\FilesystemInterface $filesystem
	 * @param string                                $filePath
	 */
	public function __construct(
		League\Flysystem\FilesystemInterface $filesystem,
		string $filePath
	) {
		$this->filesystem = $filesystem;
		$this->filePath = $filePath;
	}

	/************** interface \Nette\Application\IResponse **************/

	/**
	 * {@inheritdoc}
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
	{
		$stream = $this->filesystem->readStream($this->filePath);

		if (FALSE === $stream) {
			(new ErrorResponse('File not found.', Nette\Http\IResponse::S404_NOT_FOUND))->send($httpRequest, $httpResponse);

			return;
		}

		$metadata = $this->filesystem->getMetadata($this->filePath);

		$httpResponse
			->setHeader('Content-Type', $metadata['mimetype'] ?? $this->filesystem->getMimetype($this->filePath))
			->setHeader('Content-Length', $metadata['size'] ?? $this->filesystem->getSize($this->filePath))
			->setHeader('Cache-Control', 'max-age=31536000, public')
			->setHeader('Expires', (new \DateTime('+1 years'))->format('D, d M Y H:i:s') .' GMT');

		if (0 !== ftell($stream)) {
			rewind($stream);
		}

		fpassthru($stream);
		fclose($stream);
	}
}
