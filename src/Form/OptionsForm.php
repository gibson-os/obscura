<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Form;

use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AbstractParameter;
use GibsonOS\Core\Dto\Parameter\OptionParameter;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Form\AbstractForm;
use GibsonOS\Core\Mapper\ModelMapper;
use GibsonOS\Module\Obscura\Dto\Option\EnumValue;
use GibsonOS\Module\Obscura\Dto\Option\RangeValue;
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
        $fields = [];
        $options = $this->optionStore
            ->setDeviceName($this->deviceName)
            ->getList()
        ;

        foreach ($options as $option) {
            $name = $option->getName();
            $field = match ($option->getValue()::class) {
                EnumValue::class => new OptionParameter($name, $option->getValue()->getAllowedValues()),
                RangeValue::class => new StringParameter($name),
            };
            $field->setValue($option->getDefault());
            $fields[$name] = $field;
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
            'buttons' => ['scan' => new Button('Scan', 'obscura', 'scanner', 'scan')],
        ];
    }

    public function getButtons(): array
    {
        return [];
    }
}
