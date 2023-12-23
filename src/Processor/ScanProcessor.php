<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Module\Obscura\Enum\Format;

interface ScanProcessor
{
    public function scan(
        string $deviceName,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): void;

    public function supports(Format $format): bool;
}
