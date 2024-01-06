<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Exception\PdfException;

class PdfService
{
    public function __construct(
        private readonly ProcessService $processService,
        private readonly FileService $fileServices,
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
     * @throws PdfException
     */
    public function tiff2pdf(string $tiffFilename, string $pdfFilename): void
    {
        if (!$this->fileServices->exists($tiffFilename)) {
            throw new PdfException(sprintf('TIFF file "%s" does not exists!', $tiffFilename));
        }

        if ($this->fileServices->exists($pdfFilename)) {
            throw new PdfException(sprintf('PDF file "%s" already exists!', $pdfFilename));
        }

        $this->processService->execute(sprintf(
            '%s -o %s %s',
            $this->tiff2PdfPath,
            escapeshellarg($pdfFilename),
            escapeshellarg($tiffFilename),
        ));

        if (!$this->fileServices->exists($pdfFilename)) {
            throw new PdfException(sprintf('PDF file "%s" not created!', $pdfFilename));
        }
    }

    /**
     * @throws ProcessError
     * @throws PdfException
     */
    public function ocrPdf(string $inputFilename, string $outputFilename): void
    {
        if (!$this->fileServices->exists($inputFilename)) {
            throw new PdfException(sprintf('OCR input PDF file "%s" does not exists!', $inputFilename));
        }

        if ($this->fileServices->exists($outputFilename)) {
            throw new PdfException(sprintf('OCR PDF file "%s" already exists!', $outputFilename));
        }

        $this->processService->execute(sprintf(
            '%s %s %s -l deu+eng --image-dpi 300 --deskew --clean --rotate-pages',
            $this->ocrMyPdfPath,
            escapeshellarg($inputFilename),
            escapeshellarg($outputFilename),
        ));

        if (!$this->fileServices->exists($outputFilename)) {
            throw new PdfException(sprintf('OCR PDF file "%s" not created!', $outputFilename));
        }
    }

    /**
     * @throws ProcessError
     * @throws PdfException
     */
    public function pdfUnite(array $pdfFilenames, string $filename): void
    {
        foreach ($pdfFilenames as $pdfFilename) {
            if (!$this->fileServices->exists($pdfFilename)) {
                throw new PdfException(sprintf('Input PDF file "%s" does not exists!', $pdfFilename));
            }
        }

        if ($this->fileServices->exists($filename)) {
            throw new PdfException(sprintf('Merged PDF file "%s" already exists!', $filename));
        }

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

        if (!$this->fileServices->exists($filename)) {
            throw new PdfException(sprintf('Merged PDF file "%s" does not exists!', $filename));
        }
    }
}
