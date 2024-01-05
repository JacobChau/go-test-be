<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class MediaType extends Enum
{
    const Image = 0;

    const Audio = 1;

    const Video = 2;
}
