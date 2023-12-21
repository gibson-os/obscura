<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class AppData extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws JsonException
     * @throws SaveError
     * @throws SelectError
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function install(string $module): Generator
    {
        $this->addApp('Obscura', 'obscura', 'scanner', 'index', 'icon_scan');

        yield new Success('Obscura apps installed!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'obscura';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
