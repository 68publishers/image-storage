<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack;

interface StackInterface
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return array
	 */
	public function getValues(): array;

	/**
	 * @return string|NULL
	 */
	public function getSourceBucketName(): ?string;

	/**
	 * @return string|NULL
	 */
	public function getCacheBucketName(): ?string;
}
