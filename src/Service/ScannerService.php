<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Service;

use GibsonOS\Core\Attribute\GetServices;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Module\Obscura\Enum\Format;
use GibsonOS\Module\Obscura\Exception\ScanException;
use GibsonOS\Module\Obscura\Processor\ScanProcessor;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\StringLoaderExtension;

class ScannerService
{
    /**
     * @param ScanProcessor[] $scanProcessors
     */
    public function __construct(
        #[GetServices(['obscura/src/Processor'], ScanProcessor::class)]
        private readonly array $scanProcessors,
        private readonly TwigService $twigService,
        private readonly DirService $dirService,
    ) {
        $this->twigService->getTwig()->addExtension(new StringLoaderExtension());
    }

    /**
     * @throws ScanException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function scan(
        string $deviceName,
        Format $format,
        string $path,
        string $filename,
        bool $multipage,
        array $options,
    ): void {
        $context = ['template' => $this->dirService->addEndSlash($path) . $filename];
        $newFileName = $this->twigService->getTwig()->render('@obscura/fileName.html.twig', $context);
        $newFileName = html_entity_decode(html_entity_decode($newFileName));
        $newFileName = str_replace(['\\', ':', '*', '?', '"', '<', '>', '|'], ' ', $newFileName);
        $newFileName = preg_replace('/\s{2,}/', ' ', $newFileName);
        $newFileName = trim(preg_replace('/ (\.|\)|\?|!)/', '$1', $newFileName));

        foreach ($this->scanProcessors as $scanProcessor) {
            if (!$scanProcessor->supports($format)) {
                continue;
            }

            $scanProcessor->scan($deviceName, $newFileName, $multipage, $options);

            return;
        }

        throw new ScanException(sprintf('No scan processor found for "%s"', $format->value));
    }
}
