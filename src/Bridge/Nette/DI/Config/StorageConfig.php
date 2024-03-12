<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;
use SixtyEightPublishers\FileStorage\Bridge\Nette\DI\Config\FilesystemConfig;

final class StorageConfig
{
    public FilesystemConfig $source_filesystem;

    public string $server;

    public bool $route;

    /** @var array<string, string> */
    public array $no_image;

    /** @var array<string, string> */
    public array $no_image_patterns;

    /** @var array<string, array<string, scalar>> */
    public array $presets;

    /** @var array<Statement> */
    public array $modifiers;

    /** @var array<Statement> */
    public array $applicators;

    /** @var array<Statement> */
    public array $validators;
}
