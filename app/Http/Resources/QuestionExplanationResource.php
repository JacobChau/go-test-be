<?php

namespace App\Http\Resources;

use App\Enums\QuestionType;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use TiMacDonald\JsonApi\JsonApiResource;

class QuestionExplanationResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'content',
    ];
}
