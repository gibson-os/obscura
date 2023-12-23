<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\BoolParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Form\AbstractForm;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Module\Explorer\Dto\Parameter\DirectoryParameter;
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
            'path' => new DirectoryParameter(),
            'duplex' => new BoolParameter('Duplex'),
            'format' => new OptionParameter(
                'Format',
                [
                    Format::PDF->name => Format::PDF->value,
                    Format::PDF_DUPLEX->name => Format::PDF_DUPLEX->value,
                    Format::TIFF->name => Format::TIFF->value,
                    Format::JPEG->name => Format::JPEG->value,
                    Format::PNG->name => Format::PNG->value,
                ],
            ),
        ];
        $options = $this->optionStore
            ->setDeviceName($this->deviceName)
            ->getList()
        ;

        foreach ($options as $option) {
            $name = $option->getName();
            $allowedValues = $option->getValue()->getAllowedValues();
            $field = match ($option->getValue()::class) {
                EnumValue::class => new OptionParameter($name, $allowedValues),
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
            'buttons' => ['scan' => new Button('Scan', 'obscura', 'scanner', 'scan', ['deviceName' => $this->deviceName])],
        ];
    }

    public function getButtons(): array
    {
        return [];
    }
}
