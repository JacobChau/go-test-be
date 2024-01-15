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
        $questionLoaded = $this->relationLoaded('questions');
        return [
            'name' => $this->name,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail,
            'totalQuestions' => $this->questions->count(),
            'duration' => $this->duration,
            'totalMarks' => $this->total_marks,
            'passMarks' => $this->pass_marks,
            'maxAttempts' => $this->max_attempts,
            'isPublished' => $this->is_published,
            'subject' => SubjectResource::make($this->subject),
            'validFrom' => $this->valid_from,
            'validTo' => $this->valid_to,
            'groupIds' => optional($this->groups)->pluck('id'),
            'questions' => $this->when($questionLoaded, $this->questions->map(/**
             * @throws InvalidEnumMemberException
             */ function ($question) {
                return [
                    'id' => $question->id,
                    'marks' => $question->pivot->marks,
                    'content' => $question->content,
                    'type' => QuestionType::getKey($question->type),
                ];
            })),
        ];
    }
}
