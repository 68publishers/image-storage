<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use SixtyEightPublishers\FileStorage\Config\ConfigInterface;
use SixtyEightPublishers\FileStorage\Exception\FileNotFoundException;
use SixtyEightPublishers\ImageStorage\Config\Config;
use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use SixtyEightPublishers\ImageStorage\Exception\ResponseException;
use SixtyEightPublishers\ImageStorage\Exception\SignatureException;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\ImageStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Persistence\ImagePersisterInterface;
use Throwable;
use function assert;
use function count;
use function explode;
use function implode;
use function is_string;
use function ltrim;
use function strlen;
use function strncmp;
use function substr;

final class LocalImageServer implements ImageServerInterface
{
    public function __construct(
        private readonly ImageStorageInterface $imageStorage,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

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
     * @throws FileNotFoundException
     * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
     * @throws SignatureException
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
                    $pathInfo->withModifiers(null)->getPath(),
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
            $this->imageStorage->getConfig(),
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
     * @throws InvalidArgumentException
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
     * @throws FileNotFoundException
     * @throws \SixtyEightPublishers\FileStorage\Exception\FilesystemException
     */
    private function getFilePath(PathInfoInterface $pathInfo): string
    {
        if (true === $this->imageStorage->exists($pathInfo)) {
            return $pathInfo->getPath();
        }

        return $this->imageStorage->save(
            $this->imageStorage->createResource($pathInfo),
        );
    }

    /**
     * @throws SignatureException
     */
    private function validateSignature(RequestInterface $request, string $path): void
    {
        $signatureStrategy = $this->imageStorage->getSignatureStrategy();

        if (null === $signatureStrategy) {
            return;
        }

        $signatureParameterName = $this->imageStorage->getConfig()[Config::SIGNATURE_PARAMETER_NAME];
        assert(is_string($signatureParameterName));

        $token = (string) ($request->getQueryParameter($signatureParameterName) ?? ''); # @phpstan-ignore-line

        if (!$signatureStrategy->verifyToken($token, $path)) {
            throw new SignatureException('Request contains invalid signature.');
        }
    }
}
