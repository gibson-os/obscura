<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Model;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Module\Obscura\Enum\Format;
use JsonSerializable;

#[Table]
#[Key(true, ['name', 'vendor', 'model'])]
class Template extends AbstractModel implements JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 64)]
    private string $name;

    #[Column(length: 128)]
    private string $vendor;

    #[Column(length: 128)]
    private string $model;

    #[Column(length: 256)]
    private string $path;

    #[Column(length: 64)]
    private string $filename;

    #[Column]
    private bool $multipage;

    #[Column]
    private Format $format;

    #[Column]
    private array $options;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Template
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Template
    {
        $this->name = $name;

        return $this;
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function setVendor(string $vendor): Template
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): Template
    {
        $this->model = $model;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): Template
    {
        $this->path = $path;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Template
    {
        $this->filename = $filename;

        return $this;
    }

    public function isMultipage(): bool
    {
        return $this->multipage;
    }

    public function setMultipage(bool $multipage): Template
    {
        $this->multipage = $multipage;

        return $this;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function setFormat(Format $format): Template
    {
        $this->format = $format;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): Template
    {
        $this->options = $options;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'vendor' => $this->getVendor(),
            'model' => $this->getModel(),
            'path' => $this->getPath(),
            'filename' => $this->getFilename(),
            'multipage' => $this->isMultipage(),
            'options' => $this->getOptions(),
        ];
    }
}
