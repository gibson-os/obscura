<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Store\OptionStore;
use GibsonOS\Module\Obscura\Store\ScannerStore;

class ScannerController extends AbstractController
{
    /**
     * @throws ProcessError
     */
    #[CheckPermission([Permission::READ])]
    public function get(ScannerStore $scannerStore): AjaxResponse
    {
        return $this->returnSuccess($scannerStore->getList(), $scannerStore->getCount());
    }

    /**
     * @throws ProcessError
     * @throws OptionValueException
     */
    #[CheckPermission([Permission::READ])]
    public function getOptions(
        OptionStore $optionStore,
        string $deviceName,
    ): AjaxResponse {
        $optionStore->setDeviceName($deviceName);

        return $this->returnSuccess($optionStore->getList(), $optionStore->getCount());
    }
}
