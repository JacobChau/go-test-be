<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TiMacDonald\JsonApi\JsonApiResource;

class QuestionOptionResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'answer' => $this->answer,
            'isCorrect' => $this->is_correct,
            'blankOrder'=> $this->when($this->blank_order !== null, $this->blank_order),
        ];
    }
}
