<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use function unlink;

final class TmpFile
{
    private bool $unlinked = false;

    public function __construct(
        public readonly string $filename,
    ) {}

    /**
     * Destroy a tmp file
     */
    public function unlink(): void
    {
        if (false === $this->unlinked) {
            @unlink($this->filename);

            $this->unlinked = true;
        }
    }

    public function __destruct()
    {
        $this->unlink();
    }
}
