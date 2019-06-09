<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

final class AssetException extends \Exception implements IException
{
	/**
	 * @param string $from
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Exception\AssetException
	 */
	public static function invalidAsset(string $from): self
	{
		return new static(sprintf(
			'Invalid asset path %s',
			$from
		));
	}
}
