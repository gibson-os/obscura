<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;

class PdfService
{
    public function __construct(
        private readonly ProcessService $processService,
        #[GetEnv('TIFF2PDF_PATH')]
        private readonly string $tiff2PdfPath,
        #[GetEnv('OCRMYPDF_PATH')]
        private readonly string $ocrMyPdfPath,
        #[GetEnv('PDF_UNITE_PATH')]
        private readonly string $pdfUnitePath,
    ) {
    }

    /**
     * @throws ProcessError
     */
    public function tiff2pdf(string $tiffFilename, string $pdfFilename): void
    {
        $this->processService->execute(sprintf(
            '%s -o %s %s',
            $this->tiff2PdfPath,
            escapeshellarg($pdfFilename),
            escapeshellarg($tiffFilename),
        ));
    }

    /**
     * @throws ProcessError
     */
    public function ocrPdf(string $inputFilename, string $outputFilename): void
    {
        $this->processService->execute(sprintf(
            '%s %s %s -l deu+eng --image-dpi 300 -c -i',
            $this->ocrMyPdfPath,
            escapeshellarg($inputFilename),
            escapeshellarg($outputFilename),
        ));
    }

    /**
     * @throws ProcessError
     */
    public function pdfUnite(array $pdfFilenames, string $filename): void
    {
        $this->processService->execute(sprintf(
            '%s %s %s',
            $this->pdfUnitePath,
            implode(
                ' ',
                array_map(
                    static fn (string $pdfFilename): string => escapeshellarg($pdfFilename),
                    $pdfFilenames,
                ),
            ),
            escapeshellarg($filename),
        ));
    }
}
