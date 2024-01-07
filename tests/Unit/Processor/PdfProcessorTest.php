<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Processor;

use Codeception\Test\Unit;
use DateTime;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Processor\PdfProcessor;
use GibsonOS\Module\Obscura\Service\PdfService;
use GibsonOS\Module\Obscura\Service\ScanService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PdfProcessorTest extends Unit
{
    use ProphecyTrait;

    private PdfProcessor $pdfProcessor;

    private ScanService|ObjectProphecy $scanService;

    private DirService|ObjectProphecy $dirService;

    private FileService|ObjectProphecy $fileService;

    private PdfService|ObjectProphecy $pdfService;

    private DateTimeService|ObjectProphecy $dateTimeService;

    protected function _before()
    {
        $this->scanService = $this->prophesize(ScanService::class);
        $this->dirService = $this->prophesize(DirService::class);
        $this->fileService = $this->prophesize(FileService::class);
        $this->pdfService = $this->prophesize(PdfService::class);
        $this->dateTimeService = $this->prophesize(DateTimeService::class);

        $this->pdfProcessor = new PdfProcessor(
            $this->dirService->reveal(),
            $this->fileService->reveal(),
            $this->scanService->reveal(),
            $this->pdfService->reveal(),
            $this->dateTimeService->reveal(),
        );
    }

    public function testSupportsTrue(): void
    {
        $this->assertTrue($this->pdfProcessor->supports(Format::PDF));
    }

    public function testSupportsFalse(): void
    {
        $this->assertFalse($this->pdfProcessor->supports(Format::PNG));
    }

    public function testScanSinglePage(): void
    {
        $date = new DateTime();
        $this->dateTimeService->get()
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;
        $this->dirService->addEndSlash(sys_get_temp_dir())
            ->shouldBeCalledOnce()
            ->willReturn('tmp/')
        ;
        $filename = 'obscuraarthur' . $date->getTimestamp();
        $this->scanService->scan('arthur', 'tmp/' . $filename . '.tiff', 'tiff', false, [])
            ->shouldBeCalledOnce()
        ;
        $tiffFilename = 'trillian.tiff';
        $this->dirService->getFiles(sys_get_temp_dir(), $filename . '*.tiff')
            ->shouldBeCalledOnce()
            ->willReturn([$tiffFilename])
        ;
        $this->pdfService->tiff2pdf($tiffFilename, $tiffFilename . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->ocrPdf($tiffFilename . '.pdf', $tiffFilename . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->pdfUnite([$tiffFilename . '.pdfocr.pdf'], 'dent')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename)
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;

        $this->pdfProcessor->scan(
            'arthur',
            'dent',
            false,
            [],
        );
    }

    public function testScanMultipage(): void
    {
        $date = new DateTime();
        $this->dateTimeService->get()
            ->shouldBeCalledOnce()
            ->willReturn($date)
        ;
        $this->dirService->addEndSlash(sys_get_temp_dir())
            ->shouldBeCalledOnce()
            ->willReturn('tmp/')
        ;
        $filename = 'obscuraarthur' . $date->getTimestamp();
        $this->scanService->scan('arthur', 'tmp/' . $filename . '.tiff', 'tiff', true, [])
            ->shouldBeCalledOnce()
        ;
        $tiffFilename1 = 'trillian.tiff';
        $tiffFilename2 = 'mcmilan.tiff';
        $this->dirService->getFiles(sys_get_temp_dir(), $filename . '*.tiff')
            ->shouldBeCalledOnce()
            ->willReturn([$tiffFilename1, $tiffFilename2])
        ;
        $this->pdfService->tiff2pdf($tiffFilename1, $tiffFilename1 . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->ocrPdf($tiffFilename1 . '.pdf', $tiffFilename1 . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename1)
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename1 . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename1 . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->tiff2pdf($tiffFilename2, $tiffFilename2 . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->ocrPdf($tiffFilename2 . '.pdf', $tiffFilename2 . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename2)
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename2 . '.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->fileService->delete($tiffFilename2 . '.pdfocr.pdf')
            ->shouldBeCalledOnce()
        ;
        $this->pdfService->pdfUnite([$tiffFilename1 . '.pdfocr.pdf', $tiffFilename2 . '.pdfocr.pdf'], 'dent')
            ->shouldBeCalledOnce()
        ;

        $this->pdfProcessor->scan(
            'arthur',
            'dent',
            true,
            [],
        );
    }
}
