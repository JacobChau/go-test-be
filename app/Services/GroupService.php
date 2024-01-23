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

    public function removeMember(int $groupId, int $userId): void
    {
        $this->model->find($groupId)->users()->detach($userId);
    }

    public function addMembers(int $groupId, array $userIds): void
    {
        $this->model->find($groupId)->users()->attach($userIds);
    }
}
