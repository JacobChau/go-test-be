<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        $startedAt = $this->whenLoaded('attempts', function () {
            return $this->attempts->find(function ($attempt) {
                return $attempt->user_id === auth()->user()->id;
            })->created_at ?? null;
        });

        return [
            'name' => $this->name,
            'thumbnail' => $this->thumbnail,
            'isPublished' => $this->is_published,
            'duration' => $this->duration,
            'totalMark' => $this->total_marks,
            'totalQuestions' => $this->questions()->count(),
            'subject' => SubjectResource::make($this->subject),
            'startedAt' => $startedAt,
            'publishedAt' => $this->when($this->is_published, $this->updated_at),
        ];
    }
}
