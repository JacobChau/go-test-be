<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'name',
        'email',
        'role',
    ];

    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ? UserRole::getKey($this->role) : UserRole::getKey(UserRole::Student),
            'email_verified_at' => $this->when($request->user()->isAdmin(), $this->email_verified_at),
        ];
    }
}
