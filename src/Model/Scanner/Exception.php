<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Model\Scanner;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Wrapper\ModelWrapper;

#[Table]
class Exception extends AbstractModel
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 128)]
    #[Key]
    private string $deviceName;

    #[Column(type: Column::TYPE_TEXT)]
    private string $exception;

    #[Column]
    private DateTimeImmutable $added;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Exception
    {
        $this->id = $id;

        return $this;
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    public function setDeviceName(string $deviceName): Exception
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    public function getException(): \Exception
    {
        return unserialize($this->exception);
    }

    public function setException(\Exception $exception): Exception
    {
        $this->exception = serialize($exception);

        return $this;
    }

    public function getAdded(): DateTimeImmutable
    {
        return $this->added;
    }

    public function setAdded(DateTimeImmutable $added): Exception
    {
        $this->added = $added;

        return $this;
    }
}
