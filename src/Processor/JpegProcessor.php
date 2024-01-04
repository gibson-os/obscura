<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Service\ScanService;

class JpegProcessor implements ScanProcessor
{
    public function __construct(private readonly ScanService $scanService)
    {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     */
    public function scan(
        string $deviceName,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        $this->scanService->scan(
            $deviceName,
            $filename,
            'jpeg',
            $multipage,
            $options,
        );
    }

    public function supports(Format $format): bool
    {
        return $format === Format::JPEG;
    }
}
