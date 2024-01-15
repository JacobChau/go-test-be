<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssessmentPolicy
{
    /**
     * If a user is an admin or owner of the assessment, they can update an assessment.
     */
    public function update(User $user, Assessment $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this assessment.');
    }

    /**
     * If a user is an admin or teacher, they can delete an assessment.
     */
    public function delete(User $user, Assessment $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to delete this assessment.');
    }
}
