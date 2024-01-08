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
            'subject' => $this->subject->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'passMarks' => $this->pass_marks,
            'totalMarks' => $this->total_marks,
            'maxAttempts' => $this->max_attempts,
            'validFrom' => $this->valid_from,
            'validTo' => $this->valid_to,
            'isPublished' => $this->is_published,
        ];
    }
}
