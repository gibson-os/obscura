<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Store;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Module\Obscura\Dto\Scanner;

class ScannerStore extends AbstractStore
{
    private ?array $list = null;

    public function __construct(
        private readonly ProcessService $processService,
        #[GetEnv('SCAN_IMAGE_PATH')]
        private readonly string $scanImagePath,
    ) {
    }

    /**
     * @throws ProcessError
     *
     * @return Scanner[]
     */
    public function getList(): array
    {
        return $this->generateList();
    }

    /**
     * @throws ProcessError
     */
    public function getCount(): int
    {
        return count($this->generateList());
    }

    /**
     * @throws ProcessError
     */
    private function generateList(): array
    {
        if (is_array($this->list)) {
            return $this->list;
        }

        $this->list = [];

        $scanImageProcess = $this->processService->open(sprintf(
            '%s --formatted-device-list="%s"',
            $this->scanImagePath,
            '%d;%v;%m;%t;%i%n',
        ), 'r');

        while ($line = fgets($scanImageProcess)) {
            $scanner = explode(';', $line);

            if (count($scanner) !== 5) {
                continue;
            }

            $this->list[] = new Scanner(
                $scanner[0],
                $scanner[1],
                $scanner[2],
                $scanner[3],
                (int) $scanner[4],
            );
        }

        $this->processService->close($scanImageProcess);

        return $this->list;
    }
}
