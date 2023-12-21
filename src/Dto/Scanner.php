<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Dto;

use JsonSerializable;

class Scanner implements JsonSerializable
{
    public function __construct(
        private readonly string $deviceName,
        private readonly string $vendor,
        private readonly string $model,
        private readonly string $type,
        private readonly int $index,
    ) {
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function jsonSerialize(): array
    {
        return [
            'deviceName' => $this->getDeviceName(),
            'vendor' => $this->getVendor(),
            'model' => $this->getModel(),
            'type' => $this->getType(),
            'index' => $this->getIndex(),
        ];
    }
}
