<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Throwable;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\Exception\SignatureException;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;
use function count;
use function ltrim;
use function assert;
use function strlen;
use function substr;
use function explode;
use function implode;
use function strncmp;
use function is_string;

final class LocalImageServer implements ImageServerInterface
{
	public function __construct(
		private readonly ImageStorageInterface $imageStorage,
		private readonly ResponseFactoryInterface $responseFactory,
	) {
	}

	public function getImageResponse(RequestInterface $request): object
	{
		try {
			return $this->processRequest($request);
		} catch (FileNotFoundException $e) {
			$e = new ResponseException('Source file not found.', 404, $e);
		} catch (SignatureException $e) {
			$e = new ResponseException($e->getMessage(), 403, $e);
		} catch (Throwable $e) {
			$e = new ResponseException('Internal server error. ' . $e->getMessage(), 500, $e);
		}

		return $this->responseFactory->createErrorResponse($e, $this->imageStorage->getConfig());
	}

	/**
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	private function processRequest(RequestInterface $request): object
	{
		$path = $this->stripBasePath($request->getUrlPath());

		$this->validateSignature($request, $path);

		$pathInfo = $this->createPathInfo($path);

		try {
			$path = $this->getFilePath($pathInfo);
		} catch (FileNotFoundException $e) {
			$modifiers = $pathInfo->getModifiers();

			try {
				$noImageInfo = $this->imageStorage->resolveNoImage(
					$pathInfo->withModifiers(null)->getPath()
				);
			} catch (InvalidArgumentException $_) {
				throw $e;
			}

			$noImageInfo = $noImageInfo->withExtension($pathInfo->getExtension());
			$path = $this->getFilePath($noImageInfo->withModifiers($modifiers));
		}

		return $this->responseFactory->createImageResponse(
			$this->imageStorage->getFilesystem(),
			ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE . $path,
			$this->imageStorage->getConfig()
		);
	}

	private function stripBasePath(string $path): string
	{
		$path = ltrim($path, '/');
		$basePath = $this->imageStorage->getConfig()[ConfigInterface::BASE_PATH];
		assert(is_string($basePath));

		if (!empty($basePath) && 0 === strncmp($path, $basePath, $basePathLength = strlen($basePath))) {
			$path = ltrim(substr($path, $basePathLength), '/');
		}

		return $path;
	}

	/**
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException
	 */
	private function createPathInfo(string $path): PathInfoInterface
	{
		$parts = explode('/', $path);

		if (2 > ($pathCount = count($parts))) {
			throw new InvalidArgumentException('Missing modifier in requested path.');
		}

		$modifiers = $parts[$pathCount -2];
		unset($parts[$pathCount - 2]);

		$pathInfo = $this->imageStorage->createPathInfo(implode('/', $parts));

		if (null === $pathInfo->getExtension()) {
			throw new InvalidArgumentException('Missing file extension in requested path.');
		}

		return $pathInfo->withEncodedModifiers($modifiers);
	}

	/**
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	private function getFilePath(PathInfoInterface $pathInfo): string
	{
		if (true === $this->imageStorage->exists($pathInfo)) {
			return $pathInfo->getPath();
		}

		return $this->imageStorage->save(
			$this->imageStorage->createResource($pathInfo)
		);
	}

	/**
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\SignatureException
	 */
	private function validateSignature(RequestInterface $request, string $path): void
	{
		$signatureStrategy = $this->imageStorage->getSignatureStrategy();

		if (null === $signatureStrategy) {
			return;
		}

		$signatureParameterName = $this->imageStorage->getConfig()[Config::SIGNATURE_PARAMETER_NAME];
		assert(is_string($signatureParameterName));

		$token = $request->getQueryParameter($signatureParameterName) ?? '';

		if (empty($token)) {
			throw new SignatureException('Missing signature in request.');
		}

		if (!$signatureStrategy->verifyToken($token, $path)) {
			throw new SignatureException('Request contains invalid signature.');
		}
	}
}
