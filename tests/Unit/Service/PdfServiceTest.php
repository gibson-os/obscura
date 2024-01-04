<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Service\PdfService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PdfServiceTest extends Unit
{
    use ProphecyTrait;

    private PdfService $pdfService;

    private ProcessService|ObjectProphecy $processService;

    protected function _before()
    {
        $this->processService = $this->prophesize(ProcessService::class);

        $this->pdfService = new PdfService(
            $this->processService->reveal(),
            'tiff2pdf',
            'ocrMyPdf',
            'pdfUnite',
        );
    }

    public function testTiff2Pdf(): void
    {
        $this->processService->execute('tiff2pdf -o "arthur.pdf" "dent.tiff"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->tiff2pdf('dent.tiff', 'arthur.pdf');
    }

    public function testOcrPdf(): void
    {
        $this->processService->execute('ocrMyPdf "arthur.pdf" "dent.pdf" -l deu+eng --image-dpi 300 --deskew --clean --rotate-pages')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->ocrPdf('arthur.pdf', 'dent.pdf');
    }

    public function testPdfUnite(): void
    {
        $this->processService->execute('pdfUnite "arthur.pdf" "dent.pdf" "ford.pdf"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }
}
