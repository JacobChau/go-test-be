<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class GroupResource extends JsonApiResource
{
    /**
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
