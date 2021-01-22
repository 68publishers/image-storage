<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

final class TmpFile
{
	/** @var string  */
	private $filename;

	/** @var bool  */
	private $unlinked = FALSE;

	/**
	 * @param string $filename
	 */
	public function __construct(string $filename)
	{
		$this->filename = $filename;
	}

	/**
	 * Destroy a tmp file
	 *
	 * @return void
	 */
	public function unlink(): void
	{
		if (FALSE === $this->unlinked) {
			@unlink($this->filename);

			$this->unlinked = TRUE;
		}
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		$this->unlink();
	}
}
