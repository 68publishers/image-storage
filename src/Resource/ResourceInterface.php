<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Resource;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\Resource\ResourceInterface as FileResourceInterface;

interface ResourceInterface extends FileResourceInterface
{
    public function getSource(): Image;

    public function getLocalFilename(): string;

    public function hasBeenModified(): bool;

    /**
     * @param string|array<string, string|numeric|bool> $modifiers
     */
    public function modifyImage(string|array $modifiers): self;
}
