<?php

namespace App\Http\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

class SubjectResource extends JsonApiResource
{
    /**
     * @var string[]
     */
    protected array $attributes = [
        'name',
        'description',
    ];
}
