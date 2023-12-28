<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Repository\Scanner;

use DateTimeImmutable;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Obscura\Model\Scanner\Exception;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class ExceptionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getByLastCheck(string $deviceName, DateTimeImmutable $date): Exception
    {
        return $this->fetchOne(
            '`device_name`=:deviceName AND `added`>=:date',
            [
                'deviceName' => $deviceName,
                'date' => $date->format('Y-m-d H:i:s'),
            ],
            Exception::class,
        );
    }
}
