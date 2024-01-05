<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function update(User $user, User $model): Response
    {
        return $user->id === $model->id || $user->isAdmin() ? Response::allow() : Response::deny('You do not have permission to update this user.');
    }

    public function delete(User $user, User $model): Response
    {
        return $user->id === $model->id || $user->isAdmin() ? Response::allow() : Response::deny('You do not have permission to delete this user.');
    }
}
