<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subject;

class SubjectService extends BaseService
{
    public function __construct(Subject $subject)
    {
        $this->model = $subject;
    }
}
