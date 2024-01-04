<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Dto\Option;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Service\ScanService;
use GibsonOS\Module\Obscura\Store\OptionStore;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ScanServiceTest extends Unit
{
    use ProphecyTrait;

    private ScanService $scanService;

    private ProcessService|ObjectProphecy $processService;

    private OptionStore|ObjectProphecy $optionStore;

    protected function _before()
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->optionStore = $this->prophesize(OptionStore::class);

        $this->scanService = new ScanService(
            'galaxy/marvin',
            $this->processService->reveal(),
            $this->optionStore->reveal(),
        );
    }

    public function testScanSinglePage(): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->processService->execute('galaxy/marvin "--device-name=arthur" "--format=ford" > "dent"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->scanService->scan(
            'arthur',
            'dent',
            'ford',
            false,
            [],
        );
    }

    /**
     * @dataProvider getBatchData
     */
    public function testScanMultipage(string $filename, string $command): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->processService->execute($command)
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->scanService->scan(
            'arthur',
            $filename,
            'ford',
            true,
            [],
        );
    }

    public function getBatchData(): array
    {
        return [
            'Without number parameter' => ['dent', 'galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=%ddent"'],
            'With number parameter' => ['dent%d', 'galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=dent%d"'],
        ];
    }

    /**
     * @dataProvider getOptionData
     */
    public function testScanWithOptions(array $scannerOptions, array $options, string $command): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn($scannerOptions)
        ;
        $this->processService->execute($command)
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;

        $this->scanService->scan(
            'arthur',
            'dent',
            'ford',
            false,
            $options,
        );
    }

    public static function getOptionData(): array
    {
        $enumValue = new Option\EnumValue(['yes', 'no']);
        $rangeValue = new Option\RangeValue(0, 42);

        return [
            'Enum option' => [
                [
                    new Option('--zaphod', 'zaphod', '', 'no', $enumValue, false),
                ],
                [],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=no" > "dent"',
            ],
            'Enum option set' => [
                [
                    new Option('--zaphod', 'zaphod', '', 'no', $enumValue, false),
                ],
                ['zaphod' => 'yes'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=yes" > "dent"',
            ],
            'Enum option single' => [
                [
                    new Option('-zaphod', 'zaphod', '', 'no', $enumValue, false),
                ],
                [],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "-zaphod no" > "dent"',
            ],
            'Enum option set single' => [
                [
                    new Option('-zaphod', 'zaphod', '', 'no', $enumValue, false),
                ],
                ['zaphod' => 'yes'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "-zaphod yes" > "dent"',
            ],
            'Range option' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                [],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=21" > "dent"',
            ],
            'Range option set min' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                ['zaphod' => '0'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=0" > "dent"',
            ],
            'Range option set max' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                ['zaphod' => '42'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=42" > "dent"',
            ],
            'Range option single' => [
                [
                    new Option('-zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                [],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "-zaphod 21" > "dent"',
            ],
            'Range option set single' => [
                [
                    new Option('-zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                ['zaphod' => '42'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "-zaphod 42" > "dent"',
            ],
            'Two options' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                    new Option('--bebblebrox', 'bebblebrox', '', 'no', $enumValue, false),
                ],
                ['zaphod' => '42', 'bebblebrox' => 'yes'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--bebblebrox=yes" "--zaphod=42" > "dent"',
            ],
            'Two options geometry' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, true),
                    new Option('--bebblebrox', 'bebblebrox', '', 'no', $enumValue, false),
                ],
                ['zaphod' => '42', 'bebblebrox' => 'yes'],
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--zaphod=42" "--bebblebrox=yes" > "dent"',
            ],
        ];
    }

    /**
     * @dataProvider getInvalidOptionData
     */
    public function testScanWithInvalidOptions(array $scannerOptions, array $options): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn($scannerOptions)
        ;

        $this->expectException(OptionValueException::class);

        $this->scanService->scan(
            'arthur',
            'dent',
            'ford',
            false,
            $options,
        );
    }

    public static function getInvalidOptionData(): array
    {
        $enumValue = new Option\EnumValue(['yes', 'no']);
        $rangeValue = new Option\RangeValue(0, 42);

        return [
            'Enum option' => [
                [
                    new Option('--zaphod', 'zaphod', '', 'no', $enumValue, false),
                ],
                ['zaphod' => 'bebblebrox'],
            ],
            'Enum option default' => [
                [
                    new Option('--zaphod', 'zaphod', '', 'maybe', $enumValue, false),
                ],
                [],
            ],
            'Range option min' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                ['zaphod' => '-1'],
            ],
            'Range option max' => [
                [
                    new Option('--zaphod', 'zaphod', '', '21', $rangeValue, false),
                ],
                ['zaphod' => '43'],
            ],
            'Range option default' => [
                [
                    new Option('--zaphod', 'zaphod', '', '420', $rangeValue, false),
                ],
                [],
            ],
        ];
    }
}
