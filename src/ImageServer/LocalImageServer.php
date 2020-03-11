<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Nette;
use SixtyEightPublishers;

final class LocalImageServer implements IImageServer
{
	use Nette\SmartObject,
		SixtyEightPublishers\ImageStorage\Security\TSignatureStrategyAware;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver  */
	private $noImageResolver;

	/** @var \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory  */
	private $resourceFactory;

	/** @var \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister  */
	private $imagePersister;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver        $noImageResolver
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory       $resourceFactory
	 * @param \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister  $imagePersister
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver $noImageResolver,
		SixtyEightPublishers\ImageStorage\Resource\IResourceFactory $resourceFactory,
		SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister $imagePersister,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->env = $env;
		$this->noImageResolver = $noImageResolver;
		$this->resourceFactory = $resourceFactory;
		$this->imagePersister = $imagePersister;
		$this->modifierFacade = $modifierFacade;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	private function stripBasePath(string $path): string
	{
		$path = ltrim($path, '/');
		$basePath = $this->env[SixtyEightPublishers\ImageStorage\Config\Env::BASE_PATH];

		if (!empty($basePath) && Nette\Utils\Strings::startsWith($path, $basePath)) {
			$path = ltrim(Nette\Utils\Strings::substring($path, Nette\Utils\Strings::length($basePath)), '/');
		}

		return $path;
	}

	/**
	 * @param string $path
	 *
	 * @return array
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\ImageInfoException
	 */
	private function parseImageInfoAndModifiers(string $path): array
	{
		$parts = explode('/', $path);

		if (2 > ($pathCount = count($parts))) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException('Missing modifier in requested path.');
		}

		$modifiers = $parts[$pathCount -2];
		unset($parts[$pathCount - 2]);

		$info = new SixtyEightPublishers\ImageStorage\ImageInfo($path = implode('/', $parts));

		if (NULL === $info->getExtension()) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException('Missing file extension in requested path.');
		}

		return [$info, $this->modifierFacade->getCodec()->decode($modifiers)];
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array                                        $modifiers
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	private function getFilePath(SixtyEightPublishers\ImageStorage\ImageInfo $info, array $modifiers): string
	{
		if (TRUE === $this->imagePersister->exists($info, $modifiers)) {
			return $info->createCachedPath($this->modifierFacade->getCodec()->encode($modifiers));
		}

		return $this->imagePersister->save(
			$this->resourceFactory->createResource($info),
			$modifiers
		);
	}

	/**
	 * @param \Nette\Http\IRequest $request
	 * @param string               $path
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\SignatureException
	 */
	private function validateSignature(Nette\Http\IRequest $request, string $path): void
	{
		if (NULL === $this->signatureStrategy) {
			return;
		}

		$token = $request->getQuery($this->env[SixtyEightPublishers\ImageStorage\Config\Env::SIGNATURE_PARAMETER_NAME], '');

		if (empty($token)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\SignatureException('Missing signature in request.');
		}

		if (!$this->signatureStrategy->verifyToken($token, $path)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\SignatureException('Request contains invalid signature.');
		}
	}

	/************** interface \SixtyEightPublishers\ImageStorage\ImageServer\IImageServer **************/

	/**
	 * {@inheritdoc}
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse
	{
		$path = $this->stripBasePath($request->getUrl()->getPath());

		$this->validateSignature($request, $path);

		[$info, $modifiers] = $this->parseImageInfoAndModifiers($path);

		try {
			$path = $this->getFilePath($info, $modifiers);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException $e) {
			$path = $this->getFilePath($this->noImageResolver->resolveNoImage((string) $info), $modifiers);
		}

		return new Response\ImageResponse($this->imagePersister->getFilesystem()->getCache(), $path);
	}
}
