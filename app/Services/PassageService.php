<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Passage;

class PassageService extends BaseService
{
    public function __construct(Passage $subject)
    {
        $this->model = $subject;
    }
}
