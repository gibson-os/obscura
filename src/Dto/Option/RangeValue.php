<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto\Option;

class RangeValue implements Value
{
    public function __construct(
        private readonly int|float $from,
        private readonly int|float $to,
    ) {
    }

    public function isValid(mixed $value): bool
    {
        return $value >= $this->from && $value <= $this->to;
    }

    public function getAllowedValues(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
