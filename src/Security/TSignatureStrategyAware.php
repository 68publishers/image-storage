<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

trait TSignatureStrategyAware
{
	/** @var \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy  */
	protected $signatureStrategy;

	/************ interface \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy ************/

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy|null $signatureStrategy
	 *
	 * @return void
	 */
	public function setSignatureStrategy(?ISignatureStrategy $signatureStrategy): void
	{
		$this->signatureStrategy = $signatureStrategy;
	}
}
