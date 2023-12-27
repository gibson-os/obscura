<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;

class PdfProcessor implements ScanProcessor
{
    public function __construct(
        private readonly ProcessService $processService,
        private readonly DirService $dirService,
        private readonly TiffProcessor $tiffProcessor,
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
            $tmpPdfFilename = $this->tiff2pdf($deviceName, $tmpTiffFilename);
            $tmpOcrPdfFilename = $this->ocrPdf($deviceName, $tmpPdfFilename);

            $pdfFileNames[] = escapeshellarg($tmpOcrPdfFilename);
            unlink($tmpTiffFilename);
            unlink($tmpPdfFilename);
        }

        $this->pdfUnite($pdfFileNames, $filename);
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

    /**
     * @throws ProcessError
     */
    private function tiff2pdf(string $deviceName, string $filename): string
    {
        $tmpFilename = sprintf(
            '%sobscura%s%d.pdf',
            $this->dirService->addEndSlash(sys_get_temp_dir()),
            preg_replace('/\W/', '', $deviceName),
            time(),
        );
        $this->processService->execute(sprintf('%s -o %s %s', $this->tiff2PdfPath, $tmpFilename, $filename));

        return $tmpFilename;
    }

    /**
     * @throws ProcessError
     */
    private function ocrPdf(string $deviceName, string $filename): string
    {
        $tmpFilename = sprintf(
            '%sobscura%s%docr.pdf',
            $this->dirService->addEndSlash(sys_get_temp_dir()),
            preg_replace('/\W/', '', $deviceName),
            time(),
        );
        $this->processService->execute(sprintf(
            '%s %s %s -l deu+eng --image-dpi 300 -c -i',
            $this->ocrMyPdfPath,
            $filename,
            $tmpFilename,
        ));

        return $tmpFilename;
    }

    /**
     * @throws ProcessError
     */
    private function pdfUnite(array $pdfFilenames, string $filename): void
    {
        $this->processService->execute(sprintf(
            '%s %s %s',
            $this->pdfUnitePath,
            implode(' ', $pdfFilenames),
            $filename,
        ));
    }
}
