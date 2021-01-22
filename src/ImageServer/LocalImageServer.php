<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Throwable;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\Exception\SignatureException;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface;
use SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface;

final class LocalImageServer implements ImageServerInterface
{
	/** @var \SixtyEightPublishers\ImageStorage\ImageStorageInterface  */
	private $imageStorage;

	/** @var \SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface  */
	private $responseFactory;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageStorageInterface                         $imageStorage
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\Response\ResponseFactoryInterface $responseFactory
	 */
	public function __construct(ImageStorageInterface $imageStorage, ResponseFactoryInterface $responseFactory)
	{
		$this->imageStorage = $imageStorage;
		$this->responseFactory = $responseFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getImageResponse(RequestInterface $request)
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
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface $request
	 *
	 * @return object
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	public function processRequest(RequestInterface $request)
	{
		$path = $this->stripBasePath($request->getUrlPath());

		$this->validateSignature($request, $path);

		$pathInfo = $this->createPathInfo($path);

		try {
			$path = $this->getFilePath($pathInfo);
		} catch (FileNotFoundException $e) {
			$modifiers = $pathInfo->getModifiers();

			try {
				$noImageInfo = $this->imageStorage->resolveNoImage($pathInfo->withModifiers(NULL)->getPath());
			} catch (InvalidArgumentException $_) {
				throw $e;
			}

			$noImageInfo->setExtension($pathInfo->getExtension());

			$path = $this->getFilePath($noImageInfo->withModifiers($modifiers));
		}

		return $this->responseFactory->createImageResponse(
			$this->imageStorage->getFilesystem(),
			ImagePersisterInterface::FILESYSTEM_PREFIX_CACHE . $path,
			$this->imageStorage->getConfig()
		);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	private function stripBasePath(string $path): string
	{
		$path = ltrim($path, '/');
		$basePath = $this->imageStorage->getConfig()[Config::BASE_PATH];

		if (!empty($basePath) && 0 === strncmp($path, $basePath, $basePathLength = strlen($basePath))) {
			$path = ltrim(substr($path, $basePathLength), '/');
		}

		return $path;
	}

	/**
	 * @param string $path
	 *
	 * @return \SixtyEightPublishers\ImageStorage\PathInfoInterface
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

		/** @var \SixtyEightPublishers\ImageStorage\PathInfoInterface $pathInfo */
		$pathInfo = $this->imageStorage->createPathInfo(implode('/', $parts));

		if (NULL === $pathInfo->getExtension()) {
			throw new InvalidArgumentException('Missing file extension in requested path.');
		}

		return $pathInfo->withEncodedModifiers($modifiers);
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\PathInfoInterface $pathInfo
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
	 */
	private function getFilePath(PathInfoInterface $pathInfo): string
	{
		if (TRUE === $this->imageStorage->exists($pathInfo)) {
			return $pathInfo->getPath();
		}

		return $this->imageStorage->save(
			$this->imageStorage->createResource($pathInfo)
		);
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageServer\Request\RequestInterface $request
	 * @param string                                                                  $path
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\SignatureException
	 */
	private function validateSignature(RequestInterface $request, string $path): void
	{
		if (NULL === $this->imageStorage->getSignatureStrategy()) {
			return;
		}

		$token = $request->getQueryParameter($this->imageStorage->getConfig()[Config::SIGNATURE_PARAMETER_NAME]) ?? '';

		if (empty($token)) {
			throw new SignatureException('Missing signature in request.');
		}

		if (!$this->imageStorage->getSignatureStrategy()->verifyToken($token, $path)) {
			throw new SignatureException('Request contains invalid signature.');
		}
	}
}
