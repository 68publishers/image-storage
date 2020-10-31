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

	/** @var int  */
	private $maxAge;

	/**
	 * @param \League\Flysystem\FilesystemInterface $filesystem
	 * @param string                                $filePath
	 * @param int                                   $maxAge
	 */
	public function __construct(
		League\Flysystem\FilesystemInterface $filesystem,
		string $filePath,
		int $maxAge
	) {
		$this->filesystem = $filesystem;
		$this->filePath = $filePath;
		$this->maxAge = $maxAge;
	}

	/************** interface \Nette\Application\IResponse **************/

	/**
	 * {@inheritdoc}
	 *
	 * @throws \League\Flysystem\FileNotFoundException
	 * @throws \Exception
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
			->setHeader('Cache-Control', sprintf('public, max-age=%s', $this->maxAge))
			->setHeader('Expires', (new \DateTime(sprintf('+%s seconds', $this->maxAge), new \DateTimeZone('GMT')))->format('D, d M Y H:i:s') .' GMT');

		if (0 !== ftell($stream)) {
			rewind($stream);
		}

		fpassthru($stream);
		fclose($stream);
	}
}
