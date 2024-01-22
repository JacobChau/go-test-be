<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    public function update(User $user, Group $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this group.');
    }

    public function delete(User $user, Group $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to delete this group.');
    }

    public function addMembers(User $user, Group $model): Response
    {
        return $user->isAdmin() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to add members to this group.');
    }
}
