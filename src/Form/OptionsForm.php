<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Form;

use GibsonOS\Core\Dto\Form;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Module\Explorer\Dto\Parameter\DirectoryParameter;
use GibsonOS\Module\Obscura\AutoComplete\TemplateAutoComplete;
use GibsonOS\Module\Obscura\Config\Form\OptionsFormConfig;
use GibsonOS\Module\Obscura\Dto\Option\EnumValue;
use GibsonOS\Module\Obscura\Dto\Option\RangeValue;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;

class OptionsForm
{
    public function __construct(
        private readonly OptionStore $optionStore,
        private readonly TemplateAutoComplete $templateAutoComplete,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     *
     * @return array<string, AbstractParameter>
     */
    protected function getFields(OptionsFormConfig $config): array
    {
        $fields = [
            'name' => (new AutoCompleteParameter('Vorlage', $this->templateAutoComplete))
                ->setParameter('vendor', $config->getVendor())
                ->setParameter('model', $config->getModel()),
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
            ->setDeviceName($config->getDeviceName())
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
    public function getForm(OptionsFormConfig $config): Form
    {

        return new Form(
            $this->getFields($config),
            $this->getButtons($config),
        );
    }

    public function getButtons(OptionsFormConfig $config): array
    {
        return [
            'scan' => new Button(
                'Scannen',
                'obscura',
                'scanner',
                'scan',
                ['deviceName' => $config->getDeviceName()],
            ),
            'save' => new Button(
                'Speichern',
                'obscura',
                'scanner',
                'template',
                [
                    'vendor' => $config->getVendor(),
                    'model' => $config->getModel(),
                ],
            ),
        ];
    }
}
