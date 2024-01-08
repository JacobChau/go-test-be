<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
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
