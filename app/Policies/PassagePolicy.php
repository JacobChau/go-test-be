<?php

namespace App\Policies;

use App\Models\Passage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PassagePolicy
{
    /**
     * If a user is an admin or teacher, they can create a passage.
     *
     * @param User $user
     * @param Passage $model
     * @return Response
     */
    public function create(User $user, Passage $model): Response
    {
        var_dump($model);
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to create a passage.');
    }

    /**
     * If a user is an admin or owner of the passage, they can update a passage.
     *
     * @param User $user
     * @param Passage $model
     * @return Response
     */
    public function update(User $user, Passage $model): Response
    {
        var_dump($model);
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this passage.');
    }

    /**
     * If a user is an admin or teacher, they can delete a passage.
     *
     * @param User $user
     * @param Passage $model
     * @return Response
     */
    public function delete(User $user, Passage $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to delete this passage.');
    }
}
