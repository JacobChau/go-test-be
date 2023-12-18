<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\QuestionExplanation;
use App\Services\BaseService;

class QuestionExplanationService extends BaseService
{
    public function __construct(QuestionExplanation $subject)
    {
        $this->model = $subject;
    }
}
