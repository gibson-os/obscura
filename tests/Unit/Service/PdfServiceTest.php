<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Exception\PdfException;
use GibsonOS\Module\Obscura\Service\PdfService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PdfServiceTest extends Unit
{
    use ProphecyTrait;

    private PdfService $pdfService;

    private ProcessService|ObjectProphecy $processService;

    private FileService|ObjectProphecy $fileService;

    protected function _before()
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->fileService = $this->prophesize(FileService::class);

        $this->pdfService = new PdfService(
            $this->processService->reveal(),
            $this->fileService->reveal(),
            'tiff2pdf',
            'ocrMyPdf',
            'pdfUnite',
        );
    }

    public function testTiff2PdfSuccess(): void
    {
        $this->fileService->exists('dent.tiff')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->processService->execute('tiff2pdf -o "arthur.pdf" "dent.tiff"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->tiff2pdf('dent.tiff', 'arthur.pdf');
    }

    public function testTiff2PdfNotCreated(): void
    {
        $this->fileService->exists('dent.tiff')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, false)
        ;
        $this->processService->execute('tiff2pdf -o "arthur.pdf" "dent.tiff"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->tiff2pdf('dent.tiff', 'arthur.pdf');
    }

    public function testTiff2PdfAlreadyExists(): void
    {
        $this->fileService->exists('dent.tiff')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->tiff2pdf('dent.tiff', 'arthur.pdf');
    }

    public function testTiff2PdfNotFound(): void
    {
        $this->fileService->exists('dent.tiff')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->tiff2pdf('dent.tiff', 'arthur.pdf');
    }

    public function testOcrPdfSuccess(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->processService->execute('ocrMyPdf "arthur.pdf" "dent.pdf" -l deu+eng --image-dpi 300 --deskew --clean --rotate-pages')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->ocrPdf('arthur.pdf', 'dent.pdf');
    }

    public function testOcrPdfNotCreated(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, false)
        ;
        $this->processService->execute('ocrMyPdf "arthur.pdf" "dent.pdf" -l deu+eng --image-dpi 300 --deskew --clean --rotate-pages')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->ocrPdf('arthur.pdf', 'dent.pdf');
    }

    public function testOcrPdfAlreadyExists(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->ocrPdf('arthur.pdf', 'dent.pdf');
    }

    public function testOcrPdfNotFound(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->ocrPdf('arthur.pdf', 'dent.pdf');
    }

    public function testPdfUniteSuccess(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('ford.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, true)
        ;
        $this->processService->execute('pdfUnite "arthur.pdf" "dent.pdf" "ford.pdf"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }

    public function testPdfUniteNotCreated(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('ford.pdf')
            ->shouldBeCalledTimes(2)
            ->willReturn(false, false)
        ;
        $this->processService->execute('pdfUnite "arthur.pdf" "dent.pdf" "ford.pdf"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }

    public function testPdfUniteAlreadyExists(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('ford.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }

    public function testPdfUniteFile2NotFound(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;
        $this->fileService->exists('dent.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }

    public function testPdfUniteFile1NotFound(): void
    {
        $this->fileService->exists('arthur.pdf')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(PdfException::class);

        $this->pdfService->pdfUnite(['arthur.pdf', 'dent.pdf'], 'ford.pdf');
    }
}
