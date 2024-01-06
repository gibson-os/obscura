<?php
declare(strict_types=1);

namespace GibsonOS\Test\Unit\Obscura\Service;

use Codeception\Test\Unit;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Module\Obscura\Dto\Option;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\ScanException;
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

    private DirService|ObjectProphecy $dirService;

    private FileService|ObjectProphecy $fileService;

    protected function _before()
    {
        $this->processService = $this->prophesize(ProcessService::class);
        $this->optionStore = $this->prophesize(OptionStore::class);
        $this->fileService = $this->prophesize(FileService::class);
        $this->dirService = $this->prophesize(DirService::class);

        $this->scanService = new ScanService(
            'galaxy/marvin',
            $this->processService->reveal(),
            $this->optionStore->reveal(),
            $this->fileService->reveal(),
            $this->dirService->reveal(),
        );
    }

    /**
     * @dataProvider getScanSinglePageData
     */
    public function testScanSinglePage(string $path, string $filename, string $fileEnding): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->fileService->getFileEnding($path)
            ->shouldBeCalledOnce()
            ->willReturn($fileEnding)
        ;
        $this->fileService->getFilename($path)
            ->shouldBeCalledOnce()
            ->willReturn($filename)
        ;
        $this->processService->execute(sprintf('galaxy/marvin "--device-name=arthur" "--format=ford" > "%s"', $path))
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->fileService->exists($path)
            ->shouldBeCalledOnce()
            ->willReturn(true)
        ;

        $this->scanService->scan(
            'arthur',
            $path,
            'ford',
            false,
            [],
        );
    }

    public static function getScanSinglePageData(): array
    {
        return [
            ['arthur/dent', 'dent', 'dent'],
            ['arthur/dent.mrv', 'dent.mrv', 'mrv'],
        ];
    }

    public function testScanSinglePageNoDocument(): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->fileService->getFileEnding('arthur/dent')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->fileService->getFilename('arthur/dent')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->processService->execute('galaxy/marvin "--device-name=arthur" "--format=ford" > "arthur/dent"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->fileService->exists('arthur/dent')
            ->shouldBeCalledOnce()
            ->willReturn(false)
        ;

        $this->expectException(ScanException::class);

        $this->scanService->scan(
            'arthur',
            'arthur/dent',
            'ford',
            false,
            [],
        );
    }

    public function testScanMultipageWithNumberParameter(): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->fileService->getFileEnding('arthur/dent%d')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->processService->execute('galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=arthur/dent%d"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->dirService->getDirName('arthur/dent%d')
            ->shouldBeCalledOnce()
            ->willReturn('arthur')
        ;
        $this->fileService->getFilename('arthur/dent%d')
            ->shouldBeCalledTimes(2)
            ->willReturn('dent%d')
        ;
        $this->dirService->getFiles('arthur', 'dent*')
            ->shouldBeCalledOnce()
            ->willReturn(['dent'])
        ;

        $this->scanService->scan(
            'arthur',
            'arthur/dent%d',
            'ford',
            true,
            [],
        );
    }

    public function testScanMultipageWithNumberParameterNoDocuments(): void
    {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->fileService->getFileEnding('arthur/dent%d')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->processService->execute('galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=arthur/dent%d"')
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->dirService->getDirName('arthur/dent%d')
            ->shouldBeCalledOnce()
            ->willReturn('arthur')
        ;
        $this->fileService->getFilename('arthur/dent%d')
            ->shouldBeCalledTimes(2)
            ->willReturn('dent%d')
        ;
        $this->dirService->getFiles('arthur', 'dent*')
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;

        $this->expectException(ScanException::class);

        $this->scanService->scan(
            'arthur',
            'arthur/dent%d',
            'ford',
            true,
            [],
        );
    }

    /**
     * @dataProvider getBatchData
     */
    public function testScanMultipageWithoutNumberParameter(
        string $path,
        string $command,
        string $fileEnding,
        string $filename,
        string $dir,
        string $argumentPath,
        string $argumentFilename,
        string $pattern,
    ): void {
        $this->optionStore->setDeviceName('arthur')
            ->shouldBeCalledOnce()
        ;
        $this->optionStore->getList()
            ->shouldBeCalledOnce()
            ->willReturn([])
        ;
        $this->fileService->getFileEnding($path)
            ->shouldBeCalledOnce()
            ->willReturn($fileEnding)
        ;
        $this->fileService->getFileName($path)
            ->shouldBeCalledOnce()
            ->willReturn($filename)
        ;
        $this->processService->execute($command)
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->dirService->getDirName($path)
            ->shouldBeCalledOnce()
            ->willReturn($dir)
        ;
        $this->fileService->getFilename($argumentPath)
            ->shouldBeCalledOnce()
            ->willReturn($argumentFilename)
        ;
        $this->dirService->getFiles($dir, $pattern)
            ->shouldBeCalledOnce()
            ->willReturn(['mrv'])
        ;

        $this->scanService->scan(
            'arthur',
            $path,
            'ford',
            true,
            [],
        );
    }

    public function getBatchData(): array
    {
        return [
            'With file ending' => [
                'arthur/dent.mrv',
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=arthur/dent_%d.mrv"',
                'mrv',
                'dent.mrv',
                'arthur',
                'arthur/dent_%d.mrv',
                'dent_%d.mrv',
                'dent_*.mrv',
            ],
            'Without file ending' => [
                'arthur/dent',
                'galaxy/marvin "--device-name=arthur" "--format=ford" "--batch=arthur/dent_%d"',
                'dent',
                'dent',
                'arthur',
                'arthur/dent_%d',
                'dent_%d',
                'dent_*',
            ],
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
        $this->fileService->getFileEnding('dent')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->fileService->getFileName('dent')
            ->shouldBeCalledOnce()
            ->willReturn('dent')
        ;
        $this->processService->execute($command)
            ->shouldBeCalledOnce()
            ->willReturn('')
        ;
        $this->fileService->exists('dent')
            ->shouldBeCalledOnce()
            ->willReturn(true)
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
