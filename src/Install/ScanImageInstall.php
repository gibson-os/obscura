<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class ScanImageInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $scanImagePathInput = $this->getEnvInput('SCAN_IMAGE_PATH', 'What is the scanimage path?');

        yield (new Configuration('Scan image configuration generated!'))
            ->setValue('SCAN_IMAGE_PATH', $scanImagePathInput->getValue() ?? '')
        ;
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 800;
    }
}
