<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack;

final class Stack implements StackInterface
{
	/**
	 * @param array<string, mixed> $values
	 */
	public function __construct(
		private readonly string $name,
		private readonly array $values,
		private readonly ?string $sourceBucketName = null,
		private readonly ?string $cacheBucketName = null
	) {
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValues(): array
	{
		return $this->values;
	}

	public function getSourceBucketName(): ?string
	{
		return $this->sourceBucketName;
	}

	public function getCacheBucketName(): ?string
	{
		return $this->cacheBucketName;
	}
}
