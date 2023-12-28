<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Dto\Option;
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
    ) {
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
        $this->optionStore->setDeviceName($deviceName);
        $scannerOptions = $this->optionStore->getList();
        $arguments = [
            escapeshellarg(sprintf('--device-name=%s', $deviceName)),
            escapeshellarg('--format=tiff'),
        ];

        usort(
            $scannerOptions,
            static fn (Option $scannerOptionA, Option $scannerOptionB): int => (int) $scannerOptionA->isGeometry(),
        );

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

            $arguments[] = escapeshellarg(sprintf(
                '%s=%s',
                $scannerOption->getArgument(),
                escapeshellarg($option),
            ));
        }

        $arguments[] = $multipage
            ? escapeshellarg(sprintf(
                '--batch=%s',
                mb_strpos($filename, '%d') === false ? str_replace('.', '%d.', $filename) : $filename,
            ))
            : sprintf('> %s', escapeshellarg($filename))
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
