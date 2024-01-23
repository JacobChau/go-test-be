<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class QuestionResource extends JsonApiResource
{
    /**
     * @throws InvalidEnumMemberException
     */
    public function toAttributes(Request $request): array
    {
        if ($this->type instanceof QuestionType) {
            $this->type = $this->type->value;
        }

        return [
            'content' => $this->content,
            'type' => QuestionType::getKey($this->type),
            'category' => $this->category->name,
        ];
    }
}
