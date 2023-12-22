<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto\Option;

class EnumValue implements Value
{
    public function __construct(private readonly array $allowedValues)
    {
    }

    public function isValid(mixed $value): bool
    {
        return in_array($value, $this->allowedValues);
    }

    public function getDescription(): string
    {
        return implode('|', $this->allowedValues);
    }

    public function getAllowedValues(): array
    {
        return $this->allowedValues;
    }
}
