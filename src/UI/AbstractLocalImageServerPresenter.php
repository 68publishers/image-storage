<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\UI;

use Nette;
use Tracy;
use SixtyEightPublishers;

abstract class AbstractLocalImageServerPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

	/** @var NULL|\Nette\Http\IRequest */
	protected $request;

	/** @var NULL|\SixtyEightPublishers\ImageStorage\IImageStorageProvider */
	protected $imageStorageProvider;

	/** @var string|NULL */
	protected $imageStorageName;

	/**
	 * @internal
	 *
	 * @param \Nette\Http\IRequest $request
	 *
	 * @return void
	 */
	public function injectRequest(Nette\Http\IRequest $request): void
	{
		$this->request = $request;
	}

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider
	 *
	 * @return void
	 */
	public function injectImageStorageProvider(SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider): void
	{
		$this->imageStorageProvider = $imageStorageProvider;
	}

	/******************* interface \Nette\Application\IPresenter *******************/

	/**
	 * {@inheritdoc}
	 */
	public function run(Nette\Application\Request $request): Nette\Application\IResponse
	{
		try {
			$response = $this->imageStorageProvider
				->get($this->imageStorageName)
				->getImageResponse($this->request);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FileNotFoundException $e) {
			$response = new SixtyEightPublishers\ImageStorage\ImageServer\Response\ErrorResponse(
				'File not found.',
				Nette\Http\IResponse::S404_NOT_FOUND
			);
		} catch (\Throwable $e) {
			$response = new SixtyEightPublishers\ImageStorage\ImageServer\Response\ErrorResponse(
				'Internal server error. ' . $e->getMessage(),
				Nette\Http\IResponse::S500_INTERNAL_SERVER_ERROR
			);
		}

		if (isset($e)) {
			Tracy\Debugger::log($e->getMessage(), Tracy\ILogger::ERROR);
		}

		return $response;
	}
}
