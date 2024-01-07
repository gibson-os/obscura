<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Dto\Option;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class ScanService
{
    public function __construct(
        #[GetEnv('SCAN_IMAGE_PATH')]
        private readonly string $scanImagePath,
        private readonly ProcessService $processService,
        private readonly OptionStore $optionStore,
        private readonly FileService $fileService,
        private readonly DirService $dirService,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     * @throws ScanException
     * @throws GetError
     */
    public function scan(
        string $deviceName,
        string $path,
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

        $arguments = array_merge($arguments, $this->getScannerArguments($scannerOptions, $options));
        $argumentPath = $multipage
            ? mb_strpos($path, '%d') === false
                ? $this->addCountParameterToFilename($path)
                : $path
            : $path
        ;
        $arguments[] = $multipage
            ? sprintf('"--batch=%s"', $argumentPath)
            : sprintf('> %s', escapeshellarg($path))
        ;

        $this->processService->execute(sprintf(
            '%s %s',
            $this->scanImagePath,
            implode(' ', $arguments),
        ));

        if (!$multipage) {
            if (!$this->fileService->exists($argumentPath)) {
                throw new ScanException('No documents scanned!');
            }

            return;
        }

        $files = $this->dirService->getFiles(
            $this->dirService->getDirName($path),
            str_replace('%d', '*', $this->fileService->getFilename($argumentPath)),
        );

        if (count($files) === 0) {
            throw new ScanException('No documents scanned!');
        }
    }

    private function addCountParameterToFilename(string $filename): string
    {
        $fileEnding = $this->fileService->getFileEnding($filename);

        if ($fileEnding === $this->fileService->getFilename($filename)) {
            return sprintf('%s_%%d', $filename);
        }

        $filenameWithoutEnding = mb_substr($filename, 0, -1 - mb_strlen($fileEnding));

        return $filenameWithoutEnding . '_%d.' . $fileEnding;
    }

    /**
     * @throws OptionValueException
     */
    private function getScannerArguments(array $scannerOptions, array $options): array
    {
        $arguments = [];

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

        return $arguments;
    }
}
