<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Exception;

use InvalidArgumentException as OriginalInvalidArgumentException;

final class InvalidArgumentException extends OriginalInvalidArgumentException implements ExceptionInterface
{
}
