<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class TiffProcessor implements ScanProcessor
{
    public function __construct(
        #[GetEnv('SCAN_IMAGE_PATH')]
        private readonly string $scanImagePath,
        private readonly ProcessService $processService,
        private readonly OptionStore $optionStore,
        private readonly DirService $dirService,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     */
    public function scan(
        string $deviceName,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        $this->optionStore->setDeviceName($deviceName);
        $scannerOptions = $this->optionStore->getList();
        $arguments = [
            sprintf('-d %s', escapeshellarg($deviceName)),
            '--format tiff',
        ];

        foreach ($scannerOptions as $scannerOption) {
            $option = $options[$scannerOption->getName()] ?? null;

            if ($option === null) {
                continue;
            }

            if (!$scannerOption->getValue()->isValid($option)) {
                throw new OptionValueException(sprintf(
                    'Value "%s" for "%s" is invalid! Allowed values: %s',
                    $option,
                    $scannerOption->getName(),
                    JsonUtility::encode($scannerOption->getValue()->getAllowedValues()),
                ));
            }

            $arguments[] = sprintf(
                '%s %s',
                escapeshellarg($scannerOption->getArgument()),
                escapeshellarg($option),
            );
        }

        $escapedPath = escapeshellarg($this->dirService->addEndSlash($path) . $filename);
        $arguments[] = $multipage
            ? sprintf('--batch %s', $escapedPath)
            : sprintf('> %s', $escapedPath)
        ;

        $this->processService->execute(sprintf(
            '%s %s',
            $this->scanImagePath,
            implode(' ', $arguments),
        ));
    }

    public function supports(Format $format): bool
    {
        return $format === Format::TIFF;
    }
}
