<?php

namespace App\Policies;

use App\Models\QuestionCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionCategoryPolicy
{
    /**
     * If a user is an admin or teacher, they can create a category.
     */
    public function create(User $user, QuestionCategory $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() ? Response::allow() : Response::deny('You do not have permission to create a category.');
    }

    /**
     * If a user is an admin or owner of the category, they can update a category.
     */
    public function update(User $user, QuestionCategory $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to update this category.');
    }

    /**
     * If a user is an admin or teacher, they can delete a category.
     */
    public function delete(User $user, QuestionCategory $model): Response
    {
        return $user->isAdmin() || $user->isTeacher() || $user->is($model->createdBy) ? Response::allow() : Response::deny('You do not have permission to delete this category.');
    }
}
