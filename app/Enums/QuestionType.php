<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class QuestionType extends Enum
{
    const int MultipleChoice = 0;

    const int MultipleAnswer = 1;

    const int TrueFalse = 2;

    const int FillIn = 3;

    const int Text = 4;
}
