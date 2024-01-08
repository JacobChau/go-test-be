<?php

namespace App\Http\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

class QuestionCategoryResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'name',
    ];
}
