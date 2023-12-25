<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Obscura\Model\Template;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class TemplateRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getById(int $id): Template
    {
        return $this->fetchOne('`id`=:id', ['id' => $id], Template::class);
    }

    public function findByName(string $name, string $vendor, string $model): array
    {
        return $this->fetchAll(
            '`name` LIKE :name AND `vendor`=:vendor AND `model`=:model',
            [
                'name' => $name,
                'vendor' => $vendor,
                'model' => $model,
            ],
            Template::class,
        );
    }
}
