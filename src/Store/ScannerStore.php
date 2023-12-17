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

        $output = $this->processService->execute(sprintf(
            '%s --formatted-device-list="%s"',
            $this->scanImagePath,
            '%d;%v;%m;%t;%i%n',
        ));
        $lines = explode(PHP_EOL, $output);

        foreach ($lines as $line) {
            $this->list[] = new Scanner(
                $line[0],
                $line[1],
                $line[2],
                $line[3],
                (int) $line[4],
            );
        }

        return $this->list;
    }
}
