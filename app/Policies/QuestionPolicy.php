<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * If a user is an admin or owner of the question, they can update a question.
     */
    public function update(User $user, Question $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this question.');
    }

    /**
     * If a user is an admin or teacher, they can delete a question.
     */
    public function delete(User $user, Question $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to delete this question.');
    }
}
