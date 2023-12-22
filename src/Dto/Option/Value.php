<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto\Option;

interface Value
{
    public function isValid(mixed $value): bool;

    public function getAllowedValues(): array;
}
