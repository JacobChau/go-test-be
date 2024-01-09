<?php

namespace app\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'isPublished' => $this->is_published,
            'totalQuestions' => $this->questions()->count(),
        ];
    }
}
