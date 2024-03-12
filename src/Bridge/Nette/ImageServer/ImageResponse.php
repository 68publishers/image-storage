<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer;

use DateTime;
use DateTimeZone;
use Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use Nette\Application\IResponse as ApplicationResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use function fclose;
use function fpassthru;
use function ftell;
use function rewind;
use function sprintf;

final class ImageResponse implements ApplicationResponse
{
    public function __construct(
        private readonly FilesystemReader $filesystemReader,
        private readonly string $filePath,
        private readonly int $maxAge,
    ) {}

    /**
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
            $errorResponse = new ErrorResponse(new ResponseException('Unable to read file.', IResponse::S404_NotFound, $e));
        } catch (FilesystemException $e) {
            $errorResponse = new ErrorResponse(new ResponseException('Filesystem error. ' . $e->getMessage(), IResponse::S500_InternalServerError, $e));
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
