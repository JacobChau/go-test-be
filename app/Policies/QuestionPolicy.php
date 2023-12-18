<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * If a user is an admin or owner of the question, they can update a question.
     *
     * @param User $user
     * @param Question $model
     * @return Response
     */
    public function update(User $user, Question $model): Response
    {
        var_dump($model);
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this question.');
    }

    /**
     * If a user is an admin or teacher, they can delete a question.
     *
     * @param User $user
     * @param Question $model
     * @return Response
     */
    public function delete(User $user, Question $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to delete this question.');
    }
}
