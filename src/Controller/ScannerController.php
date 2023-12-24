<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Command\ScanCommand;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Form\OptionsForm;
use JsonException;

class ScannerController extends AbstractController
{
    /**
     * @throws ProcessError
     * @throws OptionValueException
     */
    #[CheckPermission([Permission::READ])]
    public function getForm(
        OptionsForm $optionsForm,
        string $deviceName,
    ): AjaxResponse {
        $optionsForm->setDeviceName($deviceName);

        return $this->returnSuccess($optionsForm->getForm());
    }

    /**
     * @throws JsonException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postScan(
        LockService $lockService,
        CommandService $commandService,
        string $deviceName,
        Format $format,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): AjaxResponse {
        $lockName = sprintf('obscura_%s', $deviceName);

        if ($lockService->isLocked($lockName)) {
            return $this->returnFailure('Scanner is busy');
        }

        $commandService->executeAsync(
            ScanCommand::class,
            [
                'deviceName' => $deviceName,
                'format' => $format->name,
                'path' => $path,
                'filename' => $filename,
                'multipage' => $multipage,
                'options' => JsonUtility::encode($options),
            ],
        );

        return $this->returnSuccess();
    }

    public function getStatus(
        LockService $lockService,
        string $deviceName,
    ): AjaxResponse {
        $lockName = sprintf('obscura_%s', $deviceName);

        return $this->returnSuccess(['locked' => $lockService->isLocked($lockName)]);
    }
}
