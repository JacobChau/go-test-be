<?php

namespace App\Http\Resources;

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
