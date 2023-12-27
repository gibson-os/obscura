<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Command\ScanCommand;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\TemplateException;
use GibsonOS\Module\Obscura\Form\OptionsForm;
use GibsonOS\Module\Obscura\Model\Template;
use JsonException;
use MDO\Exception\RecordException;
use ReflectionException;

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
        string $vendor,
        string $model,
    ): AjaxResponse {
        $optionsForm
            ->setDeviceName($deviceName)
            ->setVendor($vendor)
            ->setModel($model)
        ;

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

    /**
     * @throws JsonException
     */
    public function getStatus(
        LockService $lockService,
        string $deviceName,
    ): AjaxResponse {
        $lockName = sprintf('obscura_%s', $deviceName);

        return $this->returnSuccess(['locked' => $lockService->isLocked($lockName)]);
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws TemplateException
     */
    public function postTemplate(
        ModelManager $modelManager,
        #[GetMappedModel(['id' => 'name'], ['name' => ''])]
        Template $templateById,
        #[GetMappedModel(['name' => 'name', 'vendor' => 'vendor', 'model' => 'model'])]
        Template $templateByName,
        bool $overwrite = false,
    ): AjaxResponse {
        $template = $templateById->getId() === null
            ? $templateByName
            : $templateById
        ;

        if ($template->getId() !== null && $overwrite === false) {
            $exception = new TemplateException(sprintf('Ex existiert bereits eine Vorlage unter dem Namen "%s"', $template->getName()));
            $exception->setType(AbstractException::QUESTION);
            $exception->setExtraParameter('vendor', $template->getVendor());
            $exception->setExtraParameter('model', $template->getModel());
            $exception->addButton('Vorlage überschreiben', 'overwrite', true);
            $exception->addButton('Abbrechen');

            throw $exception;
        }

        $modelManager->saveWithoutChildren($template);

        return $this->returnSuccess($template);
    }
}
