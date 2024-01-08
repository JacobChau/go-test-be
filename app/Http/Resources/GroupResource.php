<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TiMacDonald\JsonApi\JsonApiResource;

class GroupResource extends JsonApiResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        if ($request->user()->isAdmin()) {
            return [
                'name' => $this->name,
                'description' => $this->description,
                'memberCount' => $this->users->count(),
                'createdAt' => $this->created_at,
                'createdBy' => $this->createdBy ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                    'avatar' => $this->createdBy->avatar,
                ] : null,
            ];
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->created_at,
        ];
    }
}
