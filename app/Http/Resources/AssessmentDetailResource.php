<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentDetailResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'totalQuestions' => $this->questions()->count(),
            'duration' => $this->duration,
            'totalMarks' => $this->total_marks,
            'passMarks' => $this->pass_marks,
            'maxAttempts' => $this->max_attempts,
            'isPublished' => $this->is_published,
            'validFrom' => $this->valid_from,
            'validTo' => $this->valid_to,
        ];
    }
}
