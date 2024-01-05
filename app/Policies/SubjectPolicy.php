<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubjectPolicy
{
    /**
     * If a user is an admin or teacher, they can create a subject.
     */
    public function create(User $user, Subject $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to create a subject.');
    }

    /**
     * If a user is an admin or teacher, they can update a subject.
     */
    public function update(User $user, Subject $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to update this subject.');
    }

    /**
     * If a user is an admin or teacher, they can delete a subject.
     */
    public function delete(User $user, Subject $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to delete this subject.');
    }
}
