<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Obscura\Store\ScannerStore;

class ScannerController extends AbstractController
{
    #[CheckPermission([Permission::READ])]
    public function get(ScannerStore $scannerStore): AjaxResponse
    {
        return $this->returnSuccess($scannerStore->getList(), $scannerStore->getCount());
    }
}
