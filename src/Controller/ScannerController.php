<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Controller;

use DateTimeImmutable;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\AbstractException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Service\CommandService;
use GibsonOS\Core\Service\DateTimeService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Obscura\Command\ScanCommand;
use GibsonOS\Module\Obscura\Config\Form\OptionsFormConfig;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\OptionValueException;
use GibsonOS\Module\Obscura\Exception\TemplateException;
use GibsonOS\Module\Obscura\Form\OptionsForm;
use GibsonOS\Module\Obscura\Model\Template;
use GibsonOS\Module\Obscura\Repository\Scanner\ExceptionRepository;
use JsonException;
use MDO\Exception\ClientException;
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
        $optionsFormConfig = new OptionsFormConfig($deviceName, $vendor, $model);

        return $this->returnSuccess($optionsForm->getForm($optionsFormConfig));
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
     * @throws RecordException
     * @throws ReflectionException
     * @throws ClientException
     */
    public function getStatus(
        DateTimeService $dateTimeService,
        LockService $lockService,
        ExceptionRepository $exceptionRepository,
        string $deviceName,
        ?string $lastCheck = null,
    ): AjaxResponse {
        $lockName = sprintf('obscura_%s', $deviceName);
        $isLocked = $lockService->isLocked($lockName);

        if (!$isLocked && $lastCheck !== null) {
            $lastCheckDate = $dateTimeService->get($lastCheck);

            try {
                $exception = $exceptionRepository->getByLastCheck($deviceName, $lastCheckDate);

                throw unserialize($exception->getException());
            } catch (SelectError) {
                // do nothing
            }
        }

        return $this->returnSuccess([
            'locked' => $isLocked,
            'date' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
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
        #[GetMappedModel(['name' => 'name', 'vendor' => 'vendor', 'model' => 'model'])]
        Template $template,
        bool $overwrite = false,
    ): AjaxResponse {
        if ($template->getId() !== null && $overwrite === false) {
            $exception = new TemplateException(sprintf('Es existiert bereits eine Vorlage unter dem Namen "%s"', $template->getName()));
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
