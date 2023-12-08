<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService extends BaseService
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function getVerifiedUser(): array
    {
        $query = $this->model->query()->verified(true);

        return $this->getList($query);
    }

    public function getModel(): User
    {
        return $this->model;
    }

    public function getByEmail(string $email): User | null
    {
        return $this->model->query()->email($email)->first();
    }
}
