<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Http\Resources\UserResource;
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

    public function getByEmail(string $email): ?User
    {
        return $this->model->query()->email($email)->first();
    }

    public function findByRememberToken(string $refreshToken): ?User
    {
        return $this->model->query()->rememberToken($refreshToken)->first();
    }

    public function getMembers(int $id): array
    {
        $query = $this->model->query()->whereHas('groups', function ($q) use ($id) {
            $q->where('group_id', $id);
        });

        return parent::getList(UserResource::class, request()->all(), $query);
    }

    public function getNotInGroup(int $id): array
    {
        // where role is not admin and where user is not in group and not current user
        $query = $this->model->query()
            ->where('role', '!=', UserRole::Admin)
            ->where('id', '!=', auth()->id())
            ->whereDoesntHave('groups', function ($q) use ($id) {
                $q->where('group_id', $id);
            });

        return parent::getList(UserResource::class, request()->all(), $query);
    }
}
