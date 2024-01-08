<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;

class GroupService extends BaseService
{
    public function __construct(Group $group)
    {
        $this->model = $group;
    }

    public function getModel(): Group
    {
        return $this->model;
    }
}
