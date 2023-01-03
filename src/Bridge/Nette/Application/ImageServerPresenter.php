<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\Application;

use Nette\Http\IRequest;
use Psr\Log\LoggerInterface;
use Nette\Application\IPresenter;
use Nette\Application\Request as ApplicationRequest;
use Nette\Application\Response as ApplicationResponse;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Request;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\ErrorResponse;
use function assert;
use function sprintf;
use function is_string;

class ImageServerPresenter implements IPresenter
{
	public function __construct(
		private readonly IRequest $request,
		private readonly FileStorageProviderInterface $fileStorageProvider,
		private readonly ?LoggerInterface $logger = null,
	) {
	}

	public function run(ApplicationRequest $request): ApplicationResponse
	{
		$storageName = $request->getParameter('__storageName');
		$storage = $this->fileStorageProvider->get(is_string($storageName) ? $storageName : null);

		if (!$storage instanceof ImageStorageInterface) {
			throw new InvalidStateException(sprintf(
				'File storage "%s" must be implementor of an interface %s.',
				$storage->getName(),
				ImageStorageInterface::class
			));
		}

		$response = $storage->getImageResponse(new Request($this->request));
		assert($response instanceof ApplicationResponse);

		if ($response instanceof ErrorResponse && null !== $this->logger) {
			$exception = $response->getException();

			$this->logger->error($exception->getMessage(), [
				'exception' => $exception,
			]);
		}

		return $response;
	}
}
