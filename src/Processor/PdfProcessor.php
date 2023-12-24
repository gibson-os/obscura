<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Attribute\GetEnv;
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
    ) {
    }

    /**
     * @throws ProcessError
     * @throws OptionValueException
     */
    public function scan(
        string $deviceName,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        $tmpTiffFilename = $this->scanTiff($deviceName, $multipage, $options);
        $tmpPdfFilename = $this->tiff2pdf($deviceName, $tmpTiffFilename);
        $this->ocrPdf($tmpPdfFilename, $path, $filename);

        unlink($tmpTiffFilename);
        unlink($tmpPdfFilename);
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
            'obscura%s%d.tiff',
            preg_replace('/\W/', '', $deviceName),
            time(),
        );
        $this->tiffProcessor->scan($deviceName, sys_get_temp_dir(), $tmpTiffFilename, $multipage, $options);

        return $tmpTiffFilename;
    }

    /**
     * @throws ProcessError
     */
    private function tiff2pdf(string $deviceName, string $filename): string
    {
        $tmpFilename = sprintf(
            'obscura%s%d.pdf',
            preg_replace('/\W/', '', $deviceName),
            time(),
        );
        $this->processService->execute(sprintf(
            '%s -o %s %s',
            $this->tiff2PdfPath,
            $this->dirService->addEndSlash(sys_get_temp_dir()) . $tmpFilename,
            $this->dirService->addEndSlash(sys_get_temp_dir()) . $filename,
        ));

        return $tmpFilename;
    }

    private function ocrPdf(string $tmpFilename, string $path, string $filename): void
    {
        $this->processService->execute(sprintf(
            '%s %s %s -l deu+eng --image-dpi 300 -c -i',
            $this->ocrMyPdfPath,
            $this->dirService->addEndSlash(sys_get_temp_dir()) . $tmpFilename,
            $this->dirService->addEndSlash($path) . $filename,
        ));
    }
}