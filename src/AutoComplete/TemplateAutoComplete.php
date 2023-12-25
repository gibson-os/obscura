<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\AutoComplete;

use GibsonOS\Core\AutoComplete\AutoCompleteInterface;
use GibsonOS\Module\Obscura\Model\Template;
use GibsonOS\Module\Obscura\Repository\TemplateRepository;

class TemplateAutoComplete implements AutoCompleteInterface
{
    public function __construct(private readonly TemplateRepository $templateRepository)
    {
    }

    public function getByNamePart(string $namePart, array $parameters): array
    {
        return $this->templateRepository->findByName($namePart, $parameters['vendor'], $parameters['model']);
    }

    public function getById(string $id, array $parameters): Template
    {
        return $this->templateRepository->getById((int) $id);
    }

    public function getModel(): string
    {
        return 'GibsonOS.module.obscura.model.Template';
    }

    public function getValueField(): string
    {
        return 'id';
    }

    public function getDisplayField(): string
    {
        return 'name';
    }
}
