<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class QuestionOptionResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'answer' => $this->answer,
            'isCorrect' => $this->when($this->is_correct !== null && ($this->question->created_by === $request->user()->id || $request->user()->role === UserRole::Admin), $this->is_correct),
            'blankOrder' => $this->when($this->blank_order !== null && ($this->question->created_by === $request->user()->id || $request->user()->role === UserRole::Admin), $this->blank_order),
        ];
    }
}
