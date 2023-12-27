<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Install\SingleInstallInterface;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class PdfUniteInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $pdfUnitePathInput = $this->getEnvInput('PDF_UNITE_PATH', 'What is the pdfunite path?');

        yield (new Configuration('tiff2pdf configuration generated!'))
            ->setValue('PDF_UNITE_PATH', $pdfUnitePathInput->getValue() ?? '')
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
