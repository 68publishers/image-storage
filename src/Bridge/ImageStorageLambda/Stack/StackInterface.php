<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack;

interface StackInterface
{
	public function getName(): string;

	/**
	 * @return array<string, mixed>
	 */
	public function getValues(): array;

	public function getSourceBucketName(): ?string;

	public function getCacheBucketName(): ?string;
}
