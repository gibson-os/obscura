<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Config\Form;

class OptionsFormConfig
{
    public function __construct(
        private readonly string $deviceName,
        private readonly string $vendor,
        private readonly string $model,
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
}
