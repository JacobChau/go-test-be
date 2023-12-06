<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class QuestionType extends Enum
{
    const MultipleChoice = 0;
    const TextAnswer = 1;
    const FillIn = 2;
    const MultipleAnswer = 3;
    const TrueFalse = 4;
}
