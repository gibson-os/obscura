<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class Tiff2PdfInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $tiff2pdfPathInput = $this->getEnvInput('TIFF2PDF_PATH', 'What is the tiff2pdf path?');

        yield (new Configuration('tiff2pdf configuration generated!'))
            ->setValue('TIFF2PDF_PATH', $tiff2pdfPathInput->getValue() ?? '')
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
