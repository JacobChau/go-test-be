<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\QuestionCategory;
use App\Services\BaseService;

class QuestionCategoryService extends BaseService
{
    public function __construct(QuestionCategory $subject)
    {
        $this->model = $subject;
    }
}
