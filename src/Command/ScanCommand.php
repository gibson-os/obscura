<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Command;

use GibsonOS\Core\Attribute\Command\Argument;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\Lock\LockException;
use GibsonOS\Core\Exception\Lock\UnlockException;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Service\ScannerService;
use JsonException;
use Psr\Log\LoggerInterface;

/**
 * @description Scan from device
 */
class ScanCommand extends AbstractCommand
{
    #[Argument('Scanner device name')]
    private string $deviceName;

    #[Argument('Format')]
    private string $format;

    #[Argument('Path')]
    private string $path;

    #[Argument('Filename')]
    private string $filename;

    #[Argument('Multipage')]
    private bool $multipage;

    #[Argument('Scanner options')]
    private string $options;

    public function __construct(
        LoggerInterface $logger,
        private readonly LockService $lockService,
        private readonly ScannerService $scannerService,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws JsonException
     * @throws LockException
     * @throws UnlockException
     * @throws ProcessError
     * @throws OptionValueException
     * @throws ScanException
     */
    protected function run(): int
    {
        $lockName = sprintf('obscura_%s', $this->deviceName);
        $this->lockService->lock($lockName);

        $this->scannerService->scan(
            $this->deviceName,
            constant(sprintf(
                '%s::%s',
                Format::class,
                $this->format,
            )),
            $this->path,
            $this->filename,
            $this->multipage,
            JsonUtility::decode($this->options),
        );

        $this->lockService->unlock($lockName);

        return self::SUCCESS;
    }

    public function setDeviceName(string $deviceName): void
    {
        $this->deviceName = $deviceName;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function setMultipage(bool $multipage): void
    {
        $this->multipage = $multipage;
    }

    public function setOptions(string $options): void
    {
        $this->options = $options;
    }
}
