<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Form\AbstractForm;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Module\Explorer\Dto\Parameter\DirectoryParameter;
use GibsonOS\Module\Obscura\AutoComplete\TemplateAutoComplete;
use GibsonOS\Module\Obscura\Dto\Option\EnumValue;
use GibsonOS\Module\Obscura\Dto\Option\RangeValue;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class OptionsForm extends AbstractForm
{
    private string $deviceName;

    public function __construct(
        ModelMapper $modelMapper,
        private readonly OptionStore $optionStore,
        private readonly TemplateAutoComplete $templateAutoComplete,
    ) {
        parent::__construct($modelMapper);
    }

    public function setDeviceName(string $deviceName): OptionsForm
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     *
     * @return array<string, AbstractParameter>
     */
    protected function getFields(): array
    {
        $fields = [
            'name' => new AutoCompleteParameter('Vorlage', $this->templateAutoComplete),
            'path' => new DirectoryParameter(),
            'filename' => new StringParameter('Dateiname'),
            'multipage' => new BoolParameter('Mehrseitig'),
            'format' => (new OptionParameter(
                'Format',
                [
                    Format::PDF->value => Format::PDF->name,
                    Format::PDF_DUPLEX->value => Format::PDF_DUPLEX->name,
                    Format::TIFF->value => Format::TIFF->name,
                    Format::JPEG->value => Format::JPEG->name,
                    Format::PNG->value => Format::PNG->name,
                ],
            ))->setValue(Format::PDF->name),
        ];
        $options = $this->optionStore
            ->setDeviceName($this->deviceName)
            ->getList()
        ;

        foreach ($options as $option) {
            $name = $option->getName();
            $allowedValues = $option->getValue()->getAllowedValues();
            $field = match ($option->getValue()::class) {
                EnumValue::class => new OptionParameter($name, array_combine($allowedValues, $allowedValues)),
                RangeValue::class => new StringParameter(sprintf(
                    '%s (%s..%s)',
                    $name,
                    $allowedValues['from'],
                    $allowedValues['to'],
                )),
            };
            $field
                ->setValue($option->getDefault())
                ->setSubText($option->getDescription())
            ;
            $fields[sprintf('options[%s]', $name)] = $field;
        }

        return $fields;
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     */
    public function getForm(): array
    {
        $fields = $this->getFields();

        return [
            'fields' => $fields,
            'buttons' => [
                'scan' => new Button('Scannen', 'obscura', 'scanner', 'scan', ['deviceName' => $this->deviceName]),
                'save' => new Button('Speichern', 'obscura', 'scanner', 'template'),
            ],
        ];
    }

    public function getButtons(): array
    {
        return [];
    }
}
