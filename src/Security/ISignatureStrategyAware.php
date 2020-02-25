<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Security;

interface ISignatureStrategyAware
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Security\ISignatureStrategy|NULL $signatureStrategy
	 *
	 * @return void
	 */
	public function setSignatureStrategy(?ISignatureStrategy $signatureStrategy): void;
}
