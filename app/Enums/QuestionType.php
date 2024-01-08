<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class QuestionType extends Enum
{
    const MultipleChoice = 0;

    const MultipleAnswer = 1;

    const TrueFalse = 2;

    const FillIn = 3;

    const Text = 4;
}
