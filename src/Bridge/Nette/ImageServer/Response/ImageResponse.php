<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Response;

use DateTime;
use Exception;
use DateTimeZone;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\FilesystemException;
use Nette\Application\IResponse as ApplicationResponse;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;

final class ImageResponse implements ApplicationResponse
{
	/** @var \League\Flysystem\FilesystemReader  */
	private $filesystemReader;

	/** @var string  */
	private $filePath;

	/** @var int  */
	private $maxAge;

	/**
	 * @param \League\Flysystem\FilesystemReader $filesystemReader
	 * @param string                             $filePath
	 * @param int                                $maxAge
	 */
	public function __construct(FilesystemReader $filesystemReader, string $filePath, int $maxAge)
	{
		$this->filesystemReader = $filesystemReader;
		$this->filePath = $filePath;
		$this->maxAge = $maxAge;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception
	 */
	public function send(IRequest $httpRequest, IResponse $httpResponse): void
	{
		try {
			$stream = $this->filesystemReader->readStream($this->filePath);

			$httpResponse
				->setHeader('Content-Type', $this->filesystemReader->mimeType($this->filePath))
				->setHeader('Content-Length', (string) $this->filesystemReader->fileSize($this->filePath))
				->setHeader('Cache-Control', sprintf('public, max-age=%s', $this->maxAge))
				->setHeader('Expires', (new DateTime(sprintf('+%s seconds', $this->maxAge), new DateTimeZone('GMT')))->format('D, d M Y H:i:s') . ' GMT');
		} catch (UnableToReadFile $e) {
			$errorResponse = new ErrorResponse(new ResponseException('Unable to read file.', IResponse::S404_NOT_FOUND, $e));
		} catch (FilesystemException $e) {
			$errorResponse = new ErrorResponse(new ResponseException('Filesystem error.', IResponse::S500_INTERNAL_SERVER_ERROR, $e));
		}

		if (isset($errorResponse)) {
			$errorResponse->send($httpRequest, $httpResponse);

			return;
		}

		if (!isset($stream)) {
			return;
		}

		if (0 !== ftell($stream)) {
			rewind($stream);
		}

		fpassthru($stream);
		fclose($stream);
	}
}
