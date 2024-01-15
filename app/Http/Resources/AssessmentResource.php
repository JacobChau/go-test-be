<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'thumbnail' => $this->thumbnail,
            'isPublished' => $this->is_published,
            'duration' => $this->duration,
            'totalMark' => $this->total_marks,
            'totalQuestions' => $this->questions()->count(),
        ];
    }
}
