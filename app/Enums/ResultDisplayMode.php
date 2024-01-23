<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class ResultDisplayMode extends Enum
{
    const HideResults = 0;

    const DisplayMarkOnly = 1;

    const DisplayMarkAndAnswers = 2;
}
