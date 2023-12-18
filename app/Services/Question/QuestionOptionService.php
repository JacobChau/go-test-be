<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\QuestionOption;
use App\Services\BaseService;

class QuestionOptionService extends BaseService
{
    public function __construct(QuestionOption $subject)
    {
        $this->model = $subject;
    }
}
