<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Collection;

use SixtyEightPublishers\ImageStorage\Exception\InvalidArgumentException;
use function sprintf;

final class ModifierValues
{
    /** @var array<string, mixed>  */
    private array $values = [];

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $k => $v) {
            $this->add($k, $v);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->values[$name]);
    }

    public function get(string $name): mixed
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf(
                'Missing value for the modifier %s.',
                $name,
            ));
        }

        return $this->values[$name];
    }

    public function getOptional(string $name, mixed $default = null): mixed
    {
        return $this->has($name) ? $this->values[$name] : $default;
    }

    private function add(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }
}
