<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class QuestionDetailResource extends JsonApiResource
{
    /**
     * @throws InvalidEnumMemberException
     */
    public function toAttributes(Request $request): array
    {
        return [
            'content' => $this->content,
            'type' => QuestionType::getKey($this->type),
            'category' => new QuestionCategoryResource($this->category),
            'explanation' => $this->when($this->explanation && $this->created_by === $request->user()->id, new QuestionExplanationResource($this->explanation)),
            'options' => QuestionOptionResource::collection($this->options),
            'passage' => $this->when($this->passage, new PassageResource($this->passage)),
        ];
    }
}
