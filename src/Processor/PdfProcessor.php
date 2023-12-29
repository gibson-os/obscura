<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Service\PdfService;

class PdfProcessor implements ScanProcessor
{
    public function __construct(
        private readonly DirService $dirService,
        private readonly TiffProcessor $tiffProcessor,
        private readonly PdfService $pdfService,
    ) {
    }

    /**
     * @throws ProcessError
     * @throws OptionValueException
     * @throws GetError
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
            unlink($tmpTiffFilename);
            unlink($tmpPdfFilename);
        }

        $this->pdfService->pdfUnite($pdfFileNames, $filename);
    }

    public function supports(Format $format): bool
    {
        return $format === Format::PDF;
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     */
    private function scanTiff(string $deviceName, bool $multipage, array $options): string
    {
        $tmpTiffFilename = sprintf(
            'obscura%s%d',
            preg_replace('/\W/', '', $deviceName),
            time(),
        );
        $this->tiffProcessor->scan(
            $deviceName,
            sprintf('%s%s.tiff', $this->dirService->addEndSlash(sys_get_temp_dir()), $tmpTiffFilename),
            $multipage,
            $options,
        );

        return $tmpTiffFilename;
    }
}
