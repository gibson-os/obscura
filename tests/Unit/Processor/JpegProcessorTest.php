<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Processor;

use Codeception\Test\Unit;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Processor\JpegProcessor;
use GibsonOS\Module\Obscura\Service\ScanService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class JpegProcessorTest extends Unit
{
    use ProphecyTrait;

    private JpegProcessor $jpegProcessor;

    private ScanService|ObjectProphecy $scanService;

    protected function _before()
    {
        $this->scanService = $this->prophesize(ScanService::class);

        $this->jpegProcessor = new JpegProcessor($this->scanService->reveal());
    }

    public function testSupportsTrue(): void
    {
        $this->assertTrue($this->jpegProcessor->supports(Format::JPEG));
    }

    public function testSupportsFalse(): void
    {
        $this->assertFalse($this->jpegProcessor->supports(Format::PNG));
    }

    public function testScanSinglePage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'jpeg', false, [])
            ->shouldBeCalledOnce()
        ;

        $this->jpegProcessor->scan(
            'arthur',
            'dent',
            false,
            [],
        );
    }

    public function testScanMultipage(): void
    {
        $this->scanService->scan('arthur', 'dent', 'jpeg', true, [])
            ->shouldBeCalledOnce()
        ;

        $this->jpegProcessor->scan(
            'arthur',
            'dent',
            true,
            [],
        );
    }
}
