<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\PdfException;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Service\PdfService;
use GibsonOS\Module\Obscura\Service\ScanService;

class PdfProcessor implements ScanProcessor
{
    public function __construct(
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly ScanService $scanService,
        private readonly PdfService $pdfService,
        private readonly DateTimeService $dateTimeService,
    ) {
    }

    /**
     * @throws GetError
     * @throws OptionValueException
     * @throws ProcessError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws PdfException
     * @throws ScanException
     */
    public function scan(
        string $deviceName,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        $tmpTiffFilePattern = $this->scanTiff($deviceName, $multipage, $options);
        $pdfFileNames = [];

        foreach ($this->dirService->getFiles(sys_get_temp_dir(), sprintf('%s*.tiff', $tmpTiffFilePattern)) as $tmpTiffFilename) {
            $tmpPdfFilename = sprintf('%s.pdf', $tmpTiffFilename);
            $this->pdfService->tiff2pdf($tmpTiffFilename, $tmpPdfFilename);
            $tmpOcrPdfFilename = sprintf('%socr.pdf', $tmpPdfFilename);
            $this->pdfService->ocrPdf($tmpPdfFilename, $tmpOcrPdfFilename);

            $pdfFileNames[] = $tmpOcrPdfFilename;
            $this->fileService->delete($tmpTiffFilename);
            $this->fileService->delete($tmpPdfFilename);
        }

        $this->pdfService->pdfUnite($pdfFileNames, $filename);

        foreach ($pdfFileNames as $pdfFileName) {
            $this->fileService->delete($pdfFileName);
        }
    }

    public function supports(Format $format): bool
    {
        return $format === Format::PDF;
    }

    /**
     * @throws GetError
     * @throws OptionValueException
     * @throws ProcessError
     * @throws ScanException
     */
    private function scanTiff(string $deviceName, bool $multipage, array $options): string
    {
        $tmpTiffFilename = sprintf(
            'obscura%s%d',
            preg_replace('/\W/', '', $deviceName),
            $this->dateTimeService->get()->getTimestamp(),
        );
        $this->scanService->scan(
            $deviceName,
            sprintf('%s%s.tiff', $this->dirService->addEndSlash(sys_get_temp_dir()), $tmpTiffFilename),
            'tiff',
            $multipage,
            $options,
        );

        return $tmpTiffFilename;
    }
}
