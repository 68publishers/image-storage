<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Bridge\Nette\DI\Config;

final class PresetConfig
{
    /** @var array<string, numeric|string|bool> */
    public array $modifiers = [];

    /** @var array<int> */
    public array $w = [];

    /** @var list<int|float>  */
    public array $x = [];

    public int|null $defaultW = null;

    public int|float|null $defaultX = null;
}
