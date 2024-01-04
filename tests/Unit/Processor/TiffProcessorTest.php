<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Processor;

use Codeception\Test\Unit;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Processor\TiffProcessor;
use GibsonOS\Module\Obscura\Service\ScanService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TiffProcessorTest extends Unit
{
    use ProphecyTrait;

    private TiffProcessor $tiffProcessor;

    private ScanService|ObjectProphecy $scanService;

    protected function _before()
    {
        $this->scanService = $this->prophesize(ScanService::class);

        $this->tiffProcessor = new TiffProcessor($this->scanService->reveal());
    }

    public function testSupportsTrue(): void
    {
        $this->assertTrue($this->tiffProcessor->supports(Format::TIFF));
    }

    public function testSupportsFalse(): void
    {
        $this->assertFalse($this->tiffProcessor->supports(Format::PNG));
    }

    public function testScanSinglePage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'tiff', false, [])
            ->shouldBeCalledOnce()
        ;

        $this->tiffProcessor->scan(
            'arthur',
            'dent',
            false,
            [],
        );
    }

    public function testScanMultipage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'tiff', true, [])
            ->shouldBeCalledOnce()
        ;

        $this->tiffProcessor->scan(
            'arthur',
            'dent',
            true,
            [],
        );
    }
}
