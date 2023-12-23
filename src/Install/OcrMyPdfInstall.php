<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class OcrMyPdfInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $ocrMyPdfPathInput = $this->getEnvInput('OCRMYPDF_PATH', 'What is the ocrmypdf path?');

        yield (new Configuration('ocrmypdf configuration generated!'))
            ->setValue('OCRMYPDF_PATH', $ocrMyPdfPathInput->getValue() ?? '')
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
