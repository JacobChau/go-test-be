<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentResultResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        $passed = $this->total_marks >= $this->assessment->pass_marks;
        return [
            'assessmentId' => $this->assessment_id,
            'name' => $this->assessment->name,
            'thumbnail' => $this->assessment->thumbnail,
            'score' => $this->total_marks,
            'totalMarks' => $this->assessment->total_marks,
            'passed' => $passed,
            'startedAt' => $this->created_at,
        ];
    }
}
