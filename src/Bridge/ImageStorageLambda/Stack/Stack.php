<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\ImageStorageLambda\Stack;

final class Stack implements StackInterface
{
	/** @var string  */
	private $name;

	/** @var array  */
	private $values;

	/** @var string|NULL  */
	private $sourceBucketName;

	/** @var string|NULL  */
	private $cacheBucketName;

	/**
	 * @param string      $name
	 * @param array       $values
	 * @param string|NULL $sourceBucketName
	 * @param string|NULL $cacheBucketName
	 */
	public function __construct(string $name, array $values, ?string $sourceBucketName = NULL, ?string $cacheBucketName = NULL)
	{
		$this->name = $name;
		$this->values = $values;
		$this->sourceBucketName = $sourceBucketName;
		$this->cacheBucketName = $cacheBucketName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSourceBucketName(): ?string
	{
		return $this->sourceBucketName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCacheBucketName(): ?string
	{
		return $this->cacheBucketName;
	}
}
