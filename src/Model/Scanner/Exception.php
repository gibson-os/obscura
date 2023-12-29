<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Model\Scanner;

use DateTimeImmutable;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Wrapper\ModelWrapper;

#[Table(engine: 'MEMORY')]
class Exception extends AbstractModel
{
    #[Column(length: 128, primary: true)]
    private string $deviceName;

    #[Column(length: 10240)]
    private string $exception;

    #[Column]
    private DateTimeInterface $added;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTimeImmutable();
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

    public function getException(): string
    {
        return $this->exception;
    }

    public function setException(string $exception): Exception
    {
        $this->exception = $exception;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Exception
    {
        $this->added = $added;

        return $this;
    }
}
