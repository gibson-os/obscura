<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Processor;

use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Service\PdfService;

class PdfDuplexProcessor implements ScanProcessor
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

            $pdfFileNames[] = escapeshellarg($tmpOcrPdfFilename);
            unlink($tmpTiffFilename);
            unlink($tmpPdfFilename);
        }

        $sortedFilenames = [];
        $reverseFilenames = array_reverse($pdfFileNames);

        foreach ($options['pdfFilenames'] ?? [] as $index => $pdfFilename) {
            $sortedFilenames[] = $pdfFilename;
            $sortedFilenames[] = $reverseFilenames[$index];
        }

        if (count($sortedFilenames) > 0) {
            $this->pdfService->pdfUnite($sortedFilenames, $filename);

            return;
        }

        throw (new ScanException('Bitte nun die geraden Seiten von hinten einlegen.'))
            ->setType(AbstractException::INFO)
            ->setExtraParameter('deviceName', $deviceName)
            ->addButton('OK', 'options[pdfFilenames]', JsonUtility::encode($pdfFileNames))
        ;
    }

    public function supports(Format $format): bool
    {
        return $format === Format::PDF_DUPLEX;
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
