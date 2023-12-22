<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Store;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\ProcessService;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Module\Obscura\Dto\Option;
use GibsonOS\Module\Obscura\Dto\Option\EnumValue;
use GibsonOS\Module\Obscura\Dto\Option\RangeValue;
use GibsonOS\Module\Obscura\Dto\Option\Value;
use GibsonOS\Module\Obscura\Exception\OptionValueException;

class OptionStore extends AbstractStore
{
    private ?array $list = null;

    private string $deviceName;

    public function __construct(
        private readonly ProcessService $processService,
        #[GetEnv('SCAN_IMAGE_PATH')]
        private readonly string $scanImagePath,
    ) {
    }

    /**
     * @throws OptionValueException
     * @throws ProcessError
     *
     * @return Option[]
     */
    public function getList(): array
    {
        return $this->generateList();
    }

    /**
     * @throws ProcessError
     * @throws OptionValueException
     */
    public function getCount(): int
    {
        return count($this->generateList());
    }

    /**
     * @throws ProcessError
     * @throws OptionValueException
     */
    private function generateList(): array
    {
        if (is_array($this->list)) {
            return $this->list;
        }

        $this->list = [];

        $scanImageProcess = $this->processService->open(sprintf(
            '%s -d %s -A',
            $this->scanImagePath,
            escapeshellarg($this->deviceName),
        ), 'r');

        $optionArgument = null;
        $optionName = '';
        $optionPossibleValues = '';
        $optionDefault = '';
        $optionDescription = '';

        while ($line = fgets($scanImageProcess)) {
            $line = trim($line);
            $line = str_replace('[=(', ' ', $line);
            $line = str_replace(')]', '', $line);

            if (preg_match('/^(--?([\w-]*))\s([^\s]*)\s\[([^]]*)\]/', $line, $hits) === 0) {
                $optionDescription .= $line . ' ';

                continue;
            }

            if ($optionArgument !== null) {
                $this->list[] = new Option(
                    $optionArgument,
                    $optionName,
                    trim($optionDescription),
                    $optionDefault,
                    $this->getOptionValue($optionPossibleValues),
                );
            }

            $optionArgument = $hits[1];
            $optionName = $hits[2];
            $optionPossibleValues = $hits[3];
            $optionDefault = $hits[4];
            $optionDescription = '';
        }

        if ($optionArgument !== null) {
            $this->list[] = new Option(
                $optionArgument,
                $optionName,
                $optionDescription,
                $optionDefault,
                $this->getOptionValue($optionPossibleValues),
            );
        }

        $this->processService->close($scanImageProcess);

        return $this->list;
    }

    public function setDeviceName(string $deviceName): OptionStore
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    private function getOptionValue(string $possibleValues): Value
    {
        if (mb_strpos($possibleValues, '|') !== false) {
            return new EnumValue($this->cleanValues(explode('|', $possibleValues)));
        }

        if (mb_strpos($possibleValues, '..') === false) {
            throw new OptionValueException(sprintf('"%s" is not allowed as option', $possibleValues));
        }

        list($from, $to) = $this->cleanValues(explode('..', $possibleValues));

        return new RangeValue((float) $from, (float) $to);
    }

    /**
     * @return array|string[]
     */
    private function cleanValues(array $allowedValues): array
    {
        if (is_numeric($allowedValues[0])) {
            $allowedValues = array_map(
                static fn (string $allowedValue): string => preg_replace('/[^\d.]/', '', $allowedValue),
                $allowedValues,
            );
        }

        return $allowedValues;
    }
}
