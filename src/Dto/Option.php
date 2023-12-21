<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto;

use GibsonOS\Module\Obscura\Dto\Option\Value;
use JsonSerializable;

class Option implements JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly string $default,
        private readonly Value $value,
    ) {
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

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'default' => $this->getDefault(),
            'value' => $this->getValue()->getDescription(),
        ];
    }
}
