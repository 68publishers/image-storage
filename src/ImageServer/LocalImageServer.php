<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\ImageServer;

use Nette;
use SixtyEightPublishers;

final class LocalImageServer implements IImageServer
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageStorage\Config\Env  */
	private $env;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver  */
	private $noImageResolver;

	/** @var \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider  */
	private $noImageProvider;

	/** @var \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory  */
	private $resourceFactory;

	/** @var \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister  */
	private $imagePersister;

	/** @var \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade  */
	private $modifierFacade;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Config\Env                      $env
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver        $noImageResolver
	 * @param \SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider        $noImageProvider
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory       $resourceFactory
	 * @param \SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister  $imagePersister
	 * @param \SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\Config\Env $env,
		SixtyEightPublishers\ImageStorage\NoImage\INoImageResolver $noImageResolver,
		SixtyEightPublishers\ImageStorage\NoImage\INoImageProvider $noImageProvider,
		SixtyEightPublishers\ImageStorage\Resource\IResourceFactory $resourceFactory,
		SixtyEightPublishers\ImageStorage\ImagePersister\IImagePersister $imagePersister,
		SixtyEightPublishers\ImageStorage\Modifier\Facade\IModifierFacade $modifierFacade
	) {
		$this->env = $env;
		$this->noImageResolver = $noImageResolver;
		$this->noImageProvider = $noImageProvider;
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
		$path = explode('/', $path);

		if (2 > ($pathCount = count($path))) {
			throw new SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException('Missing modifier in requested path.');
		}

		$modifiers = $path[$pathCount -2];
		unset($path[$pathCount - 2]);

		return [
			new SixtyEightPublishers\ImageStorage\ImageInfo(
				$path = implode('/', $path),
				$this->noImageProvider->isNoImage($path)
			),
			$this->modifierFacade->getCodec()->decode($modifiers),
		];
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\ImageInfo $info
	 * @param array                                        $modifiers
	 *
	 * @return string
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException
	 */
	private function getFilePath(SixtyEightPublishers\ImageStorage\ImageInfo $info, array $modifiers): string
	{
		if (TRUE === $this->imagePersister->exists($info, $modifiers)) {
			return $info->createPath($this->modifierFacade->getCodec()->encode($modifiers));
		}

		# if original is requested
		if (empty($modifiers)) {
			throw new SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException(
				$info->createPath($this->modifierFacade->getCodec()->encode($modifiers))
			);
		}

		return $this->imagePersister->save(
			$this->resourceFactory->createResource($info),
			$modifiers
		);
	}

	/************** interface \SixtyEightPublishers\ImageStorage\ImageServer\IImageServer **************/

	/**
	 * {@inheritdoc}
	 */
	public function getImageResponse(Nette\Http\IRequest $request): Nette\Application\IResponse
	{
		[$info, $modifiers] = $this->parseImageInfoAndModifiers(
			$this->stripBasePath($request->getUrl()->getPath())
		);

		try {
			$path = $this->getFilePath($info, $modifiers);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException $e) {
			$path = $this->getFilePath($this->noImageResolver->resolveNoImage((string) $info), $modifiers);
		}

		return new Response\ImageResponse($this->imagePersister->getFilesystem(), $path);
	}
}
