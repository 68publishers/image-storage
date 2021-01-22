<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\Presenter;

use Tracy\ILogger;
use Tracy\Debugger;
use Nette\Http\IRequest;
use Nette\Application\IPresenter;
use Nette\Application\Request as ApplicationRequest;
use Nette\Application\IResponse as ApplicationResponse;
use SixtyEightPublishers\ImageStorage\ImageStorageInterface;
use SixtyEightPublishers\FileStorage\FileStorageProviderInterface;
use SixtyEightPublishers\ImageStorage\Exception\InvalidStateException;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Request\Request;
use SixtyEightPublishers\ImageStorage\Bridge\Nette\ImageServer\Response\ErrorResponse;

abstract class AbstractImageServerPresenter implements IPresenter
{
	/**
	 * Redefine this property for specific image-storage
	 *
	 * @var string|NULL
	 */
	protected $storageName;

	/** @var \Nette\Http\IRequest  */
	private $request;

	/** @var \SixtyEightPublishers\FileStorage\FileStorageProviderInterface  */
	private $fileStorageProvider;

	/**
	 * @param \Nette\Http\IRequest                                           $request
	 * @param \SixtyEightPublishers\FileStorage\FileStorageProviderInterface $fileStorageProvider
	 */
	public function __construct(IRequest $request, FileStorageProviderInterface $fileStorageProvider)
	{
		$this->request = $request;
		$this->fileStorageProvider = $fileStorageProvider;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(ApplicationRequest $request): ApplicationResponse
	{
		$storage = $this->fileStorageProvider->get($this->storageName);

		if (!$storage instanceof ImageStorageInterface) {
			throw new InvalidStateException(sprintf(
				'File storage "%s" must be implementor is an interface %s.',
				$storage->getName(),
				ImageStorageInterface::class
			));
		}

		$response = $storage->getImageResponse(new Request($this->request));

		if ($response instanceof ErrorResponse) {
			Debugger::log($response->getException(), ILogger::ERROR);
		}

		return $response;
	}
}
