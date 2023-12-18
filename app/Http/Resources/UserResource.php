<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
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
     * @throws InvalidEnumMemberException
     */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'role' => $this->role ? UserRole::getKey($this->role) : UserRole::getKey(UserRole::Student),
            'birthdate' => $this->birthdate,
            'emailVerifiedAt' => $request->user() && ($this->when($request->user()->isAdmin(), $this->email_verified_at)),
        ];
    }
}
