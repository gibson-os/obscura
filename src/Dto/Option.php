<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto;

use GibsonOS\Module\Obscura\Dto\Option\Value;

class Option
{
    public function __construct(
        private readonly string $argument,
        private readonly string $name,
        private readonly string $description,
        private readonly string $default,
        private readonly Value $value,
    ) {
    }

    public function getArgument(): string
    {
        return $this->argument;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function getValue(): Value
    {
        return $this->value;
    }
}
