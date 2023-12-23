<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Form\OptionsForm;
use GibsonOS\Module\Obscura\Service\ScannerService;

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
     * @throws OptionValueException
     * @throws ProcessError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postScan(
        ScannerService $scannerService,
        string $deviceName,
        Format $format,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): AjaxResponse {
        $scannerService->scan(
            $deviceName,
            $format,
            $path,
            $filename,
            $multipage,
            $options,
        );

        return $this->returnSuccess();
    }
}
