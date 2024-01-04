<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Processor;

use Codeception\Test\Unit;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Processor\PngProcessor;
use GibsonOS\Module\Obscura\Service\ScanService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PngProcessorTest extends Unit
{
    use ProphecyTrait;

    private PngProcessor $pngProcessor;

    private ScanService|ObjectProphecy $scanService;

    protected function _before()
    {
        $this->scanService = $this->prophesize(ScanService::class);

        $this->pngProcessor = new PngProcessor($this->scanService->reveal());
    }

    public function testSupportsTrue(): void
    {
        $this->assertTrue($this->pngProcessor->supports(Format::PNG));
    }

    public function testSupportsFalse(): void
    {
        $this->assertFalse($this->pngProcessor->supports(Format::TIFF));
    }

    public function testScanSinglePage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'png', false, [])
            ->shouldBeCalledOnce()
        ;

        $this->pngProcessor->scan(
            'arthur',
            'dent',
            false,
            [],
        );
    }

    public function testScanMultipage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'png', true, [])
            ->shouldBeCalledOnce()
        ;

        $this->pngProcessor->scan(
            'arthur',
            'dent',
            true,
            [],
        );
    }
}
