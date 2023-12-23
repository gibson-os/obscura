<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Processor\ScanProcessor;

class ScannerService
{
    public function __construct(
        #[GetServices(['obscura/src/Processor'], ScanProcessor::class)]
        private readonly array $scanProcessors,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     * @throws ScanException
     */
    public function scan(
        string $deviceName,
        Format $format,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        foreach ($this->scanProcessors as $scanProcessor) {
            if (!$scanProcessor->supports($format)) {
                continue;
            }

            $scanProcessor->scan($deviceName, $path, $filename, $multipage, $options);

            return;
        }

        throw new ScanException(sprintf('No scan processor found for "%s"', $format->value));
    }
}
