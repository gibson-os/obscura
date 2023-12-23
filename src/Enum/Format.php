<?php
declare(strict_types=1);

namespace GibsonOS\Module\Obscura\Enum;

enum Format: string
{
    case PDF = 'pdf';
    case PDF_DUPLEX = 'pdf (Duplex)';
    case TIFF = 'tiff';
    case JPEG = 'jpg';
    case PNG = 'png';
}
