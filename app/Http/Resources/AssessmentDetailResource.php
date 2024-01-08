<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TiMacDonald\JsonApi\JsonApiResource;

class AssessmentDetailResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'subjectId' => $this->subject_id,
            'passMarks' => $this->pass_marks,
            'totalMarks' => $this->total_marks,
            'maxAttempts' => $this->max_attempts,
            'validFrom' => $this->valid_from,
            'validTo' => $this->valid_to,
            'isPublished' => $this->is_published,
            'questions' => $this->whenLoaded('questions', $this->questions->map(/**
             * @throws InvalidEnumMemberException
             */ function ($question) {
                return [
                    'id' => $question->id,
                    'marks' => $question->pivot->marks,
                    'content' => $question->content,
                    'type' => QuestionType::getKey($question->type),
                ];
            })),
            'groupIds' => $this->whenLoaded('groups', $this->groups->pluck('id')),
        ];
    }
}
