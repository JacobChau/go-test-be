<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;

class MediaService extends BaseService
{
    public function __construct(Media $subject)
    {
        $this->model = $subject;
    }
}
