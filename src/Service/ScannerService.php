<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class ScannerService
{
    public function __construct(
        #[GetEnv('SCAN_IMAGE_PATH')]
        private readonly string $scanImagePath,
        private readonly ProcessService $processService,
        private readonly OptionStore $optionStore,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     */
    public function scan(
        string $deviceName,
        Format $format,
        string $path,
        array $options,
    ): void {
        $this->optionStore->setDeviceName($deviceName);
        $scannerOptions = $this->optionStore->getList();
        $arguments = [
            sprintf('-d %s', escapeshellarg($deviceName)),
            '--format tiff',
            sprintf('--batch %s', escapeshellarg($path)),
        ];

        foreach ($scannerOptions as $scannerOption) {
            $option = $options[$scannerOption->getName()] ?? null;

            if ($option === null) {
                continue;
            }

            if (!$scannerOption->getValue()->isValid($scannerOption)) {
                throw new OptionValueException(sprintf(
                    'Value "%s" for "%s" is invalid!',
                    $option,
                    $scannerOption->getName(),
                ));
            }

            $arguments[] = sprintf(
                '%s %s',
                escapeshellarg($scannerOption->getArgument()),
                escapeshellarg($option),
            );
        }

        $this->processService->execute(sprintf(
            '%s %s',
            $this->scanImagePath,
            implode(' ', $arguments),
        ));
    }
}
