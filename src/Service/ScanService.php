<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Dto\Option;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class ScanService
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
        string $format,
        bool $multipage,
        array $options,
    ): void {
        $this->optionStore->setDeviceName($deviceName);
        $scannerOptions = $this->optionStore->getList();
        $arguments = [
            escapeshellarg(sprintf('--device-name=%s', $deviceName)),
            escapeshellarg(sprintf('--format=%s', $format)),
        ];

        usort(
            $scannerOptions,
            static fn (Option $scannerOptionA, Option $scannerOptionB): int => $scannerOptionA->isGeometry() ? -1 : 1,
        );

        foreach ($scannerOptions as $scannerOption) {
            $option = $options[$scannerOption->getName()] ?? $scannerOption->getDefault();

            if (!$scannerOption->getValue()->isValid($option)) {
                throw new OptionValueException(sprintf(
                    'Value "%s" for "%s" is invalid! Allowed values: %s',
                    $option,
                    $scannerOption->getName(),
                    JsonUtility::encode($scannerOption->getValue()->getAllowedValues()),
                ));
            }

            $arguments[] = escapeshellarg(sprintf(
                '%s%s%s',
                $scannerOption->getArgument(),
                str_starts_with($scannerOption->getArgument(), '--') ? '=' : ' ',
                $option,
            ));
        }

        $arguments[] = $multipage
            ? sprintf(
                '"--batch=%s"',
                mb_strpos($filename, '%d') === false ? '%d' . $filename : $filename,
            )
            : sprintf('> %s', escapeshellarg($filename))
        ;

        $this->processService->execute(sprintf(
            '%s %s',
            $this->scanImagePath,
            implode(' ', $arguments),
        ));
    }
}
